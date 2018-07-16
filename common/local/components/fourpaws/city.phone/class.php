<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCityPhoneComponent extends CBitrixComponent
{
    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        if (empty($params['LOCATION_CODE'])) {
            /** @var UserCitySelectInterface $userService */
            $userService = Application::getInstance()
                ->getContainer()
                ->get(FourPaws\UserBundle\Service\UserCitySelectInterface::class);
            $params['LOCATION_CODE'] = $userService->getSelectedCity()['CODE'];
        }

        return parent::onPrepareComponentParams($params);
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
            } catch (\RuntimeException $e) {}
        }
    }

    /**
     * @throws CityNotFoundException
     * @return $this
     */
    protected function prepareResult(): self
    {
        /** @var \FourPaws\LocationBundle\LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        if ($defaultCity = $locationService->getDefaultCity()) {
            $defaultLocation = $locationService->getDefaultLocation();
            if (!empty($defaultLocation)) {
                $defaultCity->withName($defaultLocation['NAME']);
            }
        } else {
            $this->abortResultCache();
            throw new CityNotFoundException('Default city not found');
        }

        $city = null;
        if ($this->arParams['LOCATION_CODE'] &&
            ($city = $locationService->getCity($this->arParams['LOCATION_CODE']))
        ) {
            $location = $locationService->findLocationCityByCode($this->arParams['LOCATION_CODE']);
            $city->withName($location['NAME']);
        }

        if (!$city) {
            $city = $defaultCity;
            $location = $defaultLocation;
        }

        /** @var \FourPaws\LocationBundle\Model\City $city */
        $phone = $city->getPhone();
        $this->arResult['CITY_NAME'] = $city->getName();
        $this->arResult['LOCATION'] = $location;
        $this->arResult['WORKING_HOURS'] = $city->getWorkingHours();
        $this->arResult['PHONE'] = PhoneHelper::formatPhone($phone);
        $this->arResult['PHONE_FOR_URL'] = PhoneHelper::formatPhone($phone, PhoneHelper::FORMAT_URL);

        /** @var \FourPaws\LocationBundle\Model\City $defaultCity */
        $defaultPhone = $defaultCity->getPhone();
        $this->arResult['DEFAULT_CITY_NAME'] = $defaultCity->getName();
        $this->arResult['DEFAULT_LOCATION'] = $defaultLocation;
        $this->arResult['DEFAULT_WORKING_HOURS'] = $defaultCity->getWorkingHours();
        $this->arResult['DEFAULT_PHONE'] = PhoneHelper::formatPhone($defaultPhone);
        $this->arResult['DEFAULT_PHONE_FOR_URL'] = PhoneHelper::formatPhone(
            $defaultPhone,
            PhoneHelper::FORMAT_URL
        );

        return $this;
    }
}
