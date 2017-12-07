<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\Helpers\PhoneHelper;

class FourPawsCityPhoneComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['LOCATION_CODE'])) {
            /** @var \FourPaws\User\UserService $userService */
            $userService = Application::getInstance()->getContainer()->get('user.service');
            $params['LOCATION_CODE'] = $userService->getSelectedCity()['CODE'];
        }

        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
                $this->prepareResult();

                $this->includeComponentTemplate();
            }
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     *
     * @throws SystemException
     */
    protected function prepareResult()
    {
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $defaultCity = $locationService->getDefaultCity();

        $city = null;
        if ($this->arParams['LOCATION_CODE']) {
            $city = $locationService->getCity($this->arParams['LOCATION_CODE']);
        } else {
            $city = $defaultCity;
        }

        if (!$city) {
            $this->AbortResultCache();
            return $this;
        }

        /** @var \FourPaws\Location\Model\City $city */
        $phone = $city->getPhone();
        $this->arResult['CITY_NAME'] = $city->getName();
        $this->arResult['PHONE'] = PhoneHelper::formatPhone($phone);
        $this->arResult['PHONE_FOR_URL'] = PhoneHelper::formatPhone($phone, PhoneHelper::FORMAT_URL);
        
        /** @var \FourPaws\Location\Model\City $defaultCity */
        $defaultPhone = $defaultCity->getPhone();
        $this->arResult['DEFAULT_CITY_NAME'] = $defaultCity->getName();
        $this->arResult['DEFAULT_PHONE'] = PhoneHelper::formatPhone($defaultPhone);
        $this->arResult['DEFAULT_PHONE_FOR_URL'] = PhoneHelper::formatPhone($defaultPhone, PhoneHelper::FORMAT_URL);

        return $this;
    }
}
