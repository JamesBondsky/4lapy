<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\LocationBundle\Model\City;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCityPhoneComponent extends CBitrixComponent
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * FourPawsCityPhoneComponent constructor.
     *
     * @param CBitrixComponent $component
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct($component)
    {
        $container = Application::getInstance()->getContainer();

        $this->userService = $container->get(UserCitySelectInterface::class);
        $this->locationService = $container->get('location.service');

        parent::__construct($component);
    }

    /**
     * @param $params
     *
     * @return array
     *
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 36000000;
        $params['LOCATION_CODE'] = $params['LOCATION_CODE'] ?? $this->userService->getSelectedCity()['CODE'];

        return parent::onPrepareComponentParams($params);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
                $this->prepareResult();

                $this->includeComponentTemplate();
            }
        } catch (Exception $e) {
            $this->abortResultCache();

            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (RuntimeException $e) {
            }
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws CityNotFoundException
     *
     * @return $this
     */
    protected function prepareResult(): self
    {
        $defaultCity = $this->locationService->getDefaultCity();

        if ($defaultCity) {
            $defaultLocation = $this->locationService->getDefaultLocation();

            if ($defaultLocation) {
                $defaultCity->withName($defaultLocation['NAME']);
            }
        } else {
            $this->abortResultCache();
            throw new CityNotFoundException('Default city not found');
        }

        $city = null;
        if ($this->arParams['LOCATION_CODE'] &&
            ($city = $this->locationService->getCity($this->arParams['LOCATION_CODE']))
        ) {
            $location = $this->locationService->findLocationCityByCode($this->arParams['LOCATION_CODE']);
            $city->withName($location['NAME']);
        }

        if (!$city) {
            $city = $defaultCity;
            $location = $defaultLocation;
        }

        /** @var City $city */
        $phone = $city->getPhone();
        $this->arResult['CITY_NAME'] = $city->getName();
        $this->arResult['LOCATION'] = $location ?? '';
        $this->arResult['WORKING_HOURS'] = $city->getWorkingHours();
        $this->arResult['PHONE'] = PhoneHelper::formatPhone($phone);
        $this->arResult['PHONE_FOR_URL'] = PhoneHelper::formatPhone($phone, PhoneHelper::FORMAT_URL);

        /** @var City $defaultCity */
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
