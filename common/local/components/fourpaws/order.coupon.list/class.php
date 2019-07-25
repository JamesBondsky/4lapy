<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

class FourPawsOrderCouponListComponent extends CBitrixComponent
{
    private $userService;

    /**
     * FourPawsOrderCouponListComponent constructor.
     *
     * @param CBitrixComponent|null $component
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $serviceContainer = Application::getInstance()->getContainer();
        $this->userService = $serviceContainer->get(CurrentUserProviderInterface::class);
    }

    public function executeComponent()
    {
        $this->arResult['SHOW'] = true;
        $this->arResult['COUPONS'] = [];
        try {
            $userID = $this->userService->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            $this->arResult['SHOW'] = false;
        }

        $this->includeComponentTemplate();
    }

}