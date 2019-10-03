<?php

use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\App\Application as App;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

/** @noinspection AutoloadingIssuesInspection */

class CStampsExchangeRules extends CBitrixComponent
{
    /**
     * @var StampService
     */
    protected $stampService;
    /**
     * @var CurrentUserProviderInterface
     */
    protected $userService;

    public function __construct($component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->stampService = $container->get(StampService::class);
        $this->userService = $container->get(CurrentUserProviderInterface::class);
    }

    public function executeComponent()
    {
        try {
            $this->userService->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            return;
        }

        $this->arResult['CURRENT_STAMP_LEVEL'] = $this->stampService->getCurrentStampLevel();

        if ($this->arResult['CURRENT_STAMP_LEVEL']) {
            $this->includeComponentTemplate();
        }
    }
}
