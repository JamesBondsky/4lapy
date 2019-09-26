<?php

use FourPaws\App\Application as App;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Symfony\Component\HttpFoundation\Request;

class DeliveryWarningComponent extends \CBitrixComponent
{
    /**
     * @var UserCitySelectInterface
     */
    protected $userCityService;
    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    protected $locationCode;

    protected const COOKIE_KEY = 'show_delivery_warning_popup';

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();

        $this->userCityService = $container->get(UserCitySelectInterface::class);
        $this->deliveryService = $container->get(DeliveryService::class);
    }

    public function executeComponent()
    {
        if ($this->wasShow()) {
            return;
        }

        $this->getCurrentLocationCode();

        if ($this->locationCode) {
            $locationZone = $this->deliveryService->getDeliveryZoneByLocation($this->locationCode);
            if ($locationZone && ($locationZone == $this->deliveryService::ZONE_DPD_EXCLUDE)) {
                $this->includeComponentTemplate();
            }
        }
    }

    protected function getCurrentLocationCode()
    {
        try {
            /** @var \FourPaws\UserBundle\Service\UserService $userService */
            $userService = App::getInstance()
                ->getContainer()
                ->get(UserCitySelectInterface::class);
            $selectedCity = $userService->getSelectedCity();
            $this->locationCode = $selectedCity['CODE'];
        } catch (Exception $e) {
            $this->locationCode = null;
        }
    }

    protected function wasShow()
    {
        $request = Request::createFromGlobals();
        $cookieValue = $request->cookies->get(self::COOKIE_KEY);

        return ($cookieValue == 1) ? true : false;
    }

    public function setShow()
    {
        setcookie(self::COOKIE_KEY, 1, time() + 24 * 60 * 60);
        }
}
