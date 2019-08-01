<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Bitrix\Main\Application as BitrixApplication;

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

/** @noinspection AutoloadingIssuesInspection */

class FourPawsOrderShelterListComponent extends FourPawsShopListComponent
{
    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * FourPawsOrderShopListComponent constructor.
     *
     * @param null $component
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     * @throws LogicException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderStorageService = $serviceContainer->get(OrderStorageService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @param array $city
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LogicException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderStorageSaveException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws FileLoaderLoadException
     * @throws ParameterNotFoundException
     * @throws RuntimeException
     * @throws SystemException
     */
    protected function prepareResult(array $city = [])
    {
        $this->arResult['SHELTERS'] = $this->arParams['SHELTERS'];
        return true;
    }
}
