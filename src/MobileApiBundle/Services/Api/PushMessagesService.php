<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\MobileApiBundle\Dto\Request\PostPushTokenRequest;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    public function __construct(
        UserService $userService,
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->userService = $userService;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
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

}
