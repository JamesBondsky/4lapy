<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\MobileApiBundle\Dto\Object\PushEventOptions;
use FourPaws\MobileApiBundle\Dto\Request\PostPushTokenRequest;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Repository\ApiPushEventRepository;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\MobileApiBundle\Dto\Object\PushEvent as PushEventForApi;

class PushMessagesService
{
    /**
     * @var UserService
     */
    private $userService;

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
        UserService $userService,
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage,
        ApiPushEventRepository $apiPushEventRepository
    )
    {
        $this->userService = $userService;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->apiPushEventRepository = $apiPushEventRepository;
    }

    /**
     * Актуализирует данные о платформе и push-токене в сессии
     * @param PostPushTokenRequest $postPushTokenRequest
     * @return bool
     */
    public function actualizeUserPushParams(PostPushTokenRequest $postPushTokenRequest)
    {
        $platform = $postPushTokenRequest->getPlatform();
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
        if (!$pushToken) {
            throw new NotFoundException('Push token is not set');
        }
        $pushEvents = $this->apiPushEventRepository->findBy([
            '=PUSH_TOKEN' => $pushToken,
            '=SUCCESS_EXEC' => ApiPushEvent::EXEC_SUCCESS_CODE,
        ]);
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
        if (!$pushToken) {
            throw new NotFoundException('Push token is not set');
        }
        $pushEvents = $this->apiPushEventRepository->findBy([
            '=ID' => $id,
            '=PUSH_TOKEN' => $pushToken,
        ], [], 1);
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
        if (!$pushToken) {
            throw new NotFoundException('Push token is not set');
        }
        $pushEvents = $this->apiPushEventRepository->findBy([
            '=ID' => $id,
            '=PUSH_TOKEN' => $pushToken,
        ], [], 1);
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
        $token = $this->tokenStorage->getToken()->getCredentials();
        $userSession = $this->apiUserSessionRepository->findByToken($token);
        return $platform !== $userSession->getPlatform() || $pushToken !== $userSession->getPushToken();
    }

    /**
     * @param $platform
     * @param $pushToken
     * @return bool
     */
    protected function updateUserPushParams($platform, $pushToken)
    {
        $token = $this->tokenStorage->getToken()->getCredentials();
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
                    ->setId($pushEvent->getMessageId())
                    ->setType($pushEvent->getMessageTypeEntity()->getXmlId())
            );
    }

}
