<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\MobileApiBundle\Dto\Object\PushEventOptions;
use FourPaws\MobileApiBundle\Dto\Request\PostPushTokenRequest;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Exception\InvalidArgumentException;
use FourPaws\MobileApiBundle\Repository\ApiPushEventRepository;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use FourPaws\MobileApiBundle\Traits\MobileApiLoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\MobileApiBundle\Dto\Object\PushEvent as PushEventForApi;

class PushMessagesService implements LoggerAwareInterface
{
    use MobileApiLoggerAwareTrait;
    
    /**
     * @var ApiUserSessionRepository
     */
    private $apiUserSessionRepository;
    
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    /**
     * @var ApiPushEventRepository
     */
    private $apiPushEventRepository;
    
    public function __construct(
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage,
        ApiPushEventRepository $apiPushEventRepository
    ) {
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage             = $tokenStorage;
        $this->apiPushEventRepository   = $apiPushEventRepository;
        $this->setLogger(LoggerFactory::create('PushMessagesService', 'mobileApi'));
    }
    
    /**
     * Актуализирует данные о платформе и push-токене в сессии
     * @param PostPushTokenRequest $postPushTokenRequest
     * @return bool
     */
    public function actualizeUserPushParams(PostPushTokenRequest $postPushTokenRequest)
    {
        $platform  = $postPushTokenRequest->getPlatform();
        $pushToken = $postPushTokenRequest->getPushToken();
        if ($this->haveUserPushParamsChanged($platform, $pushToken)) {
            return $this->updateUserPushParams($platform, $pushToken);
        }
        return true;
    }
    
    /**
     * @throws NotFoundException
     */
    public function getPushEvents()
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        if (!$session) {
            throw new NotFoundException();
        }
        $pushToken = $session->getPushToken();
        $userId    = $session->getUserId();
        if (!$pushToken && !$userId) {
            throw new NotFoundException('Push token is not set. Please run /app_launch method or set the token in database manually.');
        }
        
        $filter = [
            '=SUCCESS_EXEC'    => ApiPushEvent::EXEC_SUCCESS_CODE,
            '!MESSAGE.UF_TYPE' => null,
        ];
        
        if ($userId) {
            if ($pushToken) {
                $filter[] = [
                    'LOGIC' => 'OR',
                    [
                        '=PUSH_TOKEN' => $pushToken,
                    ],
                    [
                        '=USER_ID' => $userId,
                    ],
                ];
            } else {
                $filter[] = ['=USER_ID' => $userId];
            }
        } else {
            $filter['=PUSH_TOKEN'] = $pushToken;
        }
        
        $pushEvents = $this->apiPushEventRepository->findBy($filter, [
            'DATE_TIME_EXEC' => 'DESC',
        ]);
        
        $uniqueMessageIds = [];
        /** @var ApiPushEvent $pushEvent */
        foreach ($pushEvents as $pushEventKey => $pushEvent) {
            $messageId = $pushEvent->getMessageId();
            if (!in_array($messageId, $uniqueMessageIds, true)) {
                $uniqueMessageIds[] = $messageId;
            } else {
                unset($pushEvents[$pushEventKey]);
            }
        }
        return (new ArrayCollection($pushEvents))
            ->map(function (ApiPushEvent $pushEvent) {
                return $this->pushEventToApiFormat($pushEvent);
            })
            ->getValues();
    }
    
    /**
     * @param int $id
     * @return bool
     * @throws NotFoundException
     */
    public function markPushEventAsViewed(int $id)
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        if (!$session) {
            throw new NotFoundException();
        }
        $pushToken = $session->getPushToken();
        $userId    = $session->getUserId();
        if (!$pushToken) {
            throw new NotFoundException('Push token is not set');
        }
        
        $filter = [
            '=ID' => $id,
        ];
        
        if ($userId) {
            $filter[] = [
                'LOGIC' => 'OR',
                [
                    '=PUSH_TOKEN' => $pushToken,
                ],
                [
                    '=USER_ID' => $userId,
                ],
            ];
        } else {
            $filter['=PUSH_TOKEN'] = $pushToken;
        }
        
        $pushEvents = $this->apiPushEventRepository->findBy($filter, [], 1);
        if (!$pushEvents) {
            throw new NotFoundException("Push event with ID=$id is not found");
        }
        $pushEvent = reset($pushEvents);
        $pushEvent->setViewed(true);
        return $this->apiPushEventRepository->update($pushEvent);
    }
    
    /**
     * @param int $id
     * @return bool
     * @throws NotFoundException
     */
    public function deletePushEvent(int $id)
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        if (!$session) {
            throw new NotFoundException();
        }
        $pushToken = $session->getPushToken();
        $userId    = $session->getUserId();
        if (!$pushToken) {
            throw new NotFoundException('Push token is not set');
        }
        
        $filter = [
            '=ID' => $id,
        ];
        
        if ($userId) {
            $filter[] = [
                'LOGIC' => 'OR',
                [
                    '=PUSH_TOKEN' => $pushToken,
                ],
                [
                    '=USER_ID' => $userId,
                ],
            ];
        } else {
            $filter['=PUSH_TOKEN'] = $pushToken;
        }
        
        $pushEvents = $this->apiPushEventRepository->findBy($filter, [], 1);
        if (!$pushEvents) {
            throw new NotFoundException("Push event with ID=$id is not found");
        }
        $pushEvent = reset($pushEvents);
        return $this->apiPushEventRepository->delete($pushEvent->getId());
    }
    
    /**
     * @param $platform
     * @param $pushToken
     * @return bool
     */
    protected function haveUserPushParamsChanged($platform, $pushToken)
    {
        $token       = $this->tokenStorage->getToken()->getCredentials();
        $userSession = $this->apiUserSessionRepository->findByToken($token);
        try {
            if ($userSession) {
                $this->mobileApiLog()->info(__METHOD__ . '. $pushToken: ' . $pushToken . '. $platform: ' . $platform . '. userId: ' . $userSession->getUserId());
            } else {
                $this->mobileApiLog()->info(__METHOD__ . '. $pushToken: ' . $pushToken . '. $platform: ' . $platform . '. userSession: null');
            }
        } catch (\Exception $e) {
            $this->mobileApiLog()->info(__METHOD__ . '. $pushToken: ' . $pushToken . '. $platform: ' . $platform . '. Exception: ' . $e->getMessage());
        }
        
        return $platform !== $userSession->getPlatform() || $pushToken !== $userSession->getPushToken();
    }
    
    /**
     * @param $platform
     * @param $pushToken
     * @return bool
     */
    protected function updateUserPushParams($platform, $pushToken)
    {
        $token       = $this->tokenStorage->getToken()->getCredentials();
        $userSession = $this->apiUserSessionRepository->findByToken($token);
        $userSession
            ->setPlatform($platform)
            ->setPushToken($pushToken);
        
        return $this->apiUserSessionRepository->update($userSession);
    }
    
    protected function pushEventToApiFormat(ApiPushEvent $pushEvent)
    {
        
        
        return (new PushEventForApi())
            ->setId($pushEvent->getId())
            ->setText($pushEvent->getMessageText())
            ->setDateTimeExec($pushEvent->getDateTimeExec())
            ->setViewed($pushEvent->getViewed())
            ->setOptions(
                (new PushEventOptions())
                    ->setId($pushEvent->getEventId())
                    ->setType($pushEvent->getMessageTypeEntity()->getXmlId())
            );
    }
    
    /**
     * Отфильтровывает $pushTokens, возвращая только существующие в новой базе токены
     * @param array $pushTokens
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getExistingPushTokens(array $pushTokens)
    {
        if (!$pushTokens) {
            throw new InvalidArgumentException(__METHOD__ . '. Пустой массив токенов');
        }
        
        $rsExistingPushTokens = ApiUserSessionTable::query()
            ->setFilter([
                '=PUSH_TOKEN' => $pushTokens,
                '!USER_ID'    => '',
            ])
            ->setSelect([
                'PUSH_TOKEN',
            ])
            ->exec()
            ->fetchAll();
        $existingPushTokens   = array_values(array_unique(array_column($rsExistingPushTokens, 'PUSH_TOKEN')));
        
        return $existingPushTokens;
    }
}
