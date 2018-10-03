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

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderShopListComponent extends FourPawsShopListComponent
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
     */
    protected function prepareResult(array $city = [])
    {
        $storage = $this->orderStorageService->getStorage();
        if ($pickupDelivery = $this->orderStorageService->getPickupDelivery($storage)) {
            /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
            $router = Application::getInstance()->getContainer()->get('router');
            /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
            $storeListUrlRoute = null;
            if ($routeCollection = $router->getRouteCollection()) {
                $storeListUrlRoute = $routeCollection->get('fourpaws_sale_ajax_order_storesearch');
            }
            $this->arResult['DELIVERY'] = $pickupDelivery;
            $this->arResult['DELIVERY_CODE'] = $pickupDelivery->getDeliveryCode();
            $this->arResult['IS_DPD'] = $this->deliveryService->isDpdPickup($pickupDelivery);
            $this->arResult['STORE_LIST_URL'] = $storeListUrlRoute ? $storeListUrlRoute->getPath() : '';
        }
        return true;
    }
}
