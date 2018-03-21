<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopListComponent extends CBitrixComponent
{
    /** @var StoreService $storeService */
    protected $storeService;

    /** @var UserService $userService */
    private $userService;

    /**
     * FourPawsShopListComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();

            $this->storeService = $container->get('store.service');
            $this->userService = $container->get(UserCitySelectInterface::class);
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws SystemException
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function executeComponent()
    {
        $container = App::getInstance()->getContainer();

        $request = Application::getInstance()->getContext()->getRequest();

        $cityCode = $request->get('city') ?? 0;
        $this->arResult['ACTIVE_STORE_ID'] = $request->get('id') ?? 0;

        $city = [];
        if ($cityCode > 0) {
            /** @var LocationService $locationService */
            $locationService = $container->get('location.service');
            try {
                $city = $locationService->findLocationCityByCode($cityCode);
            } catch (CityNotFoundException $e) {
            }
        }
        if (empty($city)) {
            $city = $this->userService->getSelectedCity();
        }
        if ($this->startResultCache(false, ['location' => $city['CODE']])) {
            if ($this->prepareResult($city)) {
                $this->includeComponentTemplate();
            }
            if (\defined('BX_COMP_MANAGED_CACHE')) {
                $instance = Application::getInstance();
                $tagCache = $instance->getTaggedCache();
                $tagCache->registerTag('shop:list:'. $city['CODE']);
                $tagCache->registerTag('shop:list');
            }
        }

        return true;
    }

    /**
     * @param array $city
     * @return bool
     * @throws Exception
     */
    protected function prepareResult(array $city = [])
    {
        if (empty($city)) {
            $city = $this->userService->getSelectedCity();
        }

        $this->arResult['CITY'] = $city['NAME'];
        $this->arResult['CITY_CODE'] = $city['CODE'];

        $this->arResult['SERVICES'] = $this->storeService->getServicesInfo();
        $this->arResult['METRO'] = $this->storeService->getMetroInfo();

        return true;
    }
}
