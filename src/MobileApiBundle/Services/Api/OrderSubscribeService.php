<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\PersonalBundle\Service\OrderSubscribeService as AppOrderSubscribeService;
use FourPaws\UserBundle\Service\UserService as AppUserService;

class OrderSubscribeService
{
    /**
     * @var AppOrderSubscribeService;
     */
    private $appOrderSubscribeService;

    /**
     * @var AppUserService
     */
    private $appUserService;

    public function __construct(
        AppOrderSubscribeService $appOrderSubscribeService,
        AppUserService $appUserService
    )
    {
        $this->appOrderSubscribeService = $appOrderSubscribeService;
        $this->appUserService = $appUserService;
    }

    /**
     * @throws \Exception
     */
    public function getSubscriptionsForCurrentUser()
    {
        $user = $this->appUserService->getCurrentUser();
        $subscriptions = $this->appOrderSubscribeService->getSubscriptionsByUser($user->getId());
        var_dump($subscriptions);
    }
}
