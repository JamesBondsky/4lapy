<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Sale;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetOrdersComponent extends FourPawsComponent
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->orderService = $container->get('order.service');
        $this->basketService = $container->get(BasketService::class);
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PATH_TO_BASKET'] = '/cart/';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function prepareResult(): void
    {
        $orders = null;
        $orderCount = 0;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            $instance = Application::getInstance();

            $request = $instance->getContext()->getRequest();
            if ($request->get('reply_order') === 'Y') {
                $orderId = (int)$request->get('id');
                if ($orderId > 0) {
                    $this->copyOrder2CustomerBasket($orderId, $request);
                }
            }
            $this->orderService->loadManzanaOrders($user); //TODO del


            $navResult = new CDBResult();
            $navResult->NavNum = 'nav-more-orders';
            $navResult->NavPageSize = OrderService::ORDER_PAGE_LIMIT;
            $navResult->NavPageNomer = 1;

            $orders = $this->orderService->getUserOrders($user);
            $orderCount = $this->orderService->getUserOrdersCount($user);

            $navResult->NavRecordCount = $orderCount;
            $navResult->NavPageCount = ceil($orderCount / OrderService::ORDER_PAGE_LIMIT);

        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return;
        }

        $this->arResult['TOTAL_ORDER_COUNT'] = $orderCount;
        $this->arResult['ORDERS'] = $orders ?? new ArrayCollection();
        $this->arResult['NAV'] = $navResult;
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserProvider;
    }

    /**
     * @param OrderItem $item
     * @param int       $percent
     *
     * @return string
     */
    public function getItemBonus(OrderItem $item, int $percent): string
    {
        $bonusText = '';

        $bonus = $item->getPrice() * $item->getBonusAwardingQuantity() * $percent / 100;
        /** @var OrderItem $child */
        foreach ($item->getDetachedItems() as $child) {
            $bonus += $child->getPrice() * $child->getBonusAwardingQuantity() * $percent / 100;
        }
        $bonus = floor($bonus);

        if ($bonus > 0) {
            $bonusText = \sprintf(
                '+ %s %s',
                $bonus,
                WordHelper::declension($bonus, [
                    'бонус',
                    'бонуса',
                    'бонусов',
                ])
            );
        }

        return $bonusText;
    }

    /**
     * Взято из компонента списка заказов Битиркса common/bitrix/components/bitrix/sale.personal.order.list/class.php
     *
     * @param int $id Order id
     *
     * @param Main\Request $request
     *
     * @return void
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\NotImplementedException
     * @throws Main\NotSupportedException
     * @throws SystemException
     */
    protected function copyOrder2CustomerBasket(int $id, Main\Request $request): void
    {
        $result = new Main\Result();

        $isManzana = $request->get('is_manzana') ?? false;
        if ($id > 0 || $isManzana) {
            $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Main\Context::getCurrent()->getSite());

            if ($isManzana) {
                /** добавление товаров в корзину для манзановского заказа */
                $items = json_decode($request->get('item_ids'), true);
                if (!empty($items)) {
                    /** @var BasketService $basketService */
                    $basketService = App::getInstance()->getContainer()->get(BasketService::class);
                    foreach ($items as $item) {
                        $basketService->addOfferToBasket($item['ID'], $item['QUANTITY'], [], true, $basket);
                    }
                } else {
                    $result->addErrors([new Main\Error('нет итемов')]);
                }
            } else {
                $filterFields = [
                    'SET_PARENT_ID',
                    'TYPE',
                    'PRODUCT_ID',
                    'PRODUCT_PRICE_ID',
                    'PRICE',
                    'CURRENCY',
                    'WEIGHT',
                    'QUANTITY',
                    'LID',
                    'NAME',
                    'CALLBACK_FUNC',
                    'NOTES',
                    'PRODUCT_PROVIDER_CLASS',
                    'CANCEL_CALLBACK_FUNC',
                    'ORDER_CALLBACK_FUNC',
                    'PAY_CALLBACK_FUNC',
                    'DETAIL_PAGE_URL',
                    'CATALOG_XML_ID',
                    'PRODUCT_XML_ID',
                    'VAT_RATE',
                    'MEASURE_NAME',
                    'MEASURE_CODE',
                    'BASE_PRICE',
                    'VAT_INCLUDED',
                ];
                $filterFields = array_flip($filterFields);

                $oldOrder = Sale\Order::load($id);

                if ($oldOrder !== null) {
                    $oldBasket = $oldOrder->getBasket();
                    $oldBasketItems = $oldBasket->getBasketItems();

                    /** @var Sale\BasketItem $oldBasketItem */
                    foreach ($oldBasketItems as $oldBasketItem) {
                        if ($this->basketService->isGiftProduct($oldBasketItem)) {
                            continue;
                        }
                        $propertyList = [];
                        if ($oldPropertyCollection = $oldBasketItem->getPropertyCollection()) {
                            $propertyList = $oldPropertyCollection->getPropertyValues();
                        }

                        $item = $basket->getExistsItem($oldBasketItem->getField('MODULE'),
                            $oldBasketItem->getField('PRODUCT_ID'), $propertyList);

                        if ($item) {
                            $resultItem = $item->setField('QUANTITY',
                                $item->getQuantity() + $oldBasketItem->getQuantity());
                        } else {
                            $item = $basket->createItem($oldBasketItem->getField('MODULE'),
                                $oldBasketItem->getField('PRODUCT_ID'));
                            $oldBasketValues = array_intersect_key($oldBasketItem->getFieldValues(), $filterFields);
                            $item->setField('NAME', $oldBasketValues['NAME']);
                            $resultItem = $item->setFields($oldBasketValues);
                            $newPropertyCollection = $item->getPropertyCollection();

                            /** @var Sale\BasketPropertyItem $oldProperty */
                            foreach ($propertyList as $oldPropertyFields) {
                                $propertyItem = $newPropertyCollection->createItem([]);
                                unset($oldPropertyFields['ID'], $oldPropertyFields['BASKET_ID']);

                                /** @var Sale\BasketPropertyItem $propertyItem */
                                $propertyItem->setFields($oldPropertyFields);
                            }
                        }
                        if (!$resultItem->isSuccess()) {
                            $result->addErrors($resultItem->getErrors());
                        }
                    }
                }
            }


            if ($result->isSuccess()) {
                $basket->save();
            } else {
                throw new Main\SystemException('Невозможно копировать заказ');
            }

            LocalRedirect($this->arParams['PATH_TO_BASKET']);
        }
    }
}
