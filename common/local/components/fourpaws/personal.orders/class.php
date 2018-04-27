<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

class FourPawsPersonalCabinetOrdersComponent extends CBitrixComponent
{
    /**
     * @var OrderService
     */
    private $orderService;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /** @var UserAuthorizationInterface */
    private $currentUserProvider;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws \RuntimeException
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
            $this->orderService = $container->get('order.service');
            $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PAGE_COUNT'] = 10;
        $params['PATH_TO_BASKET'] = '/cart/';
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 24 * 60 * 60;
        /** кешируем запросы к манзане на 2 часа - можно будет увеличить, если по статистике обращений в день к странице заказов у разных пользователей будет небольшое */
        $params['MANZANA_CACHE_TIME'] = 2 * 60 * 60;

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws ObjectException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return null;
        }

        $instance = Application::getInstance();

        $request = $instance->getContext()->getRequest();
        if ($request->get('reply_order') === 'Y') {
            $orderId = (int)$request->get('id');
            if ($orderId > 0) {
                $this->copyOrder2CustomerBasket($orderId, $request);
            }
        }

        $this->setFrameMode(true);

        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            /** запрашиваем авторизацию */
            \define('NEED_AUTH', true);
            return null;
        }

        $cache = $instance->getCache();
        // здесь всегда будет работать $this->getPath(), который вернет не тот путь
        //$cachePath = $this->getCachePath() ?: $this->getPath();
        $cachePath = $instance->getManagedCache()->getCompCachePath(
            $this->getRelativePath()
        );
        if ($cache->initCache($this->arParams['MANZANA_CACHE_TIME'],
            serialize(['userId' => $userId]),
            $cachePath)
        ) {
            $result = $cache->getVars();
            $manzanaOrders = $result['manzanaOrders'];
        } elseif ($cache->startDataCache()) {
            $tagCache = null;
            if (\defined('BX_COMP_MANAGED_CACHE')) {
                $tagCache = $instance->getTaggedCache();
                $tagCache->startTagCache($cachePath);
            }
            try {
                $manzanaOrders = $this->orderService->getManzanaOrders();
            } catch (ManzanaServiceException $e) {
                $manzanaOrders = new ArrayCollection();
            }

            if ($tagCache !== null) {
                TaggedCacheHelper::addManagedCacheTags([
                    'personal:orders',
                    'personal:orders:' . $userId,
                    'order:' . $userId,
                ], $tagCache);
                $tagCache->endTagCache();
            }

            $cache->endDataCache(['manzanaOrders' => $manzanaOrders]);
        }

        /** имитация постранички */
        $nav = new PageNavigation('nav-orders');
        $nav->allowAllRecords(false)->setPageSize($this->arParams['PAGE_COUNT'])->initFromUri();
        /*
        // кешируем шаблон по номерам чеков из манзаны, ибо инфа в манзану должна передаваться всегда
        $startResultCacheRes = $this->startResultCache(
            $this->arParams['CACHE_TIME'],
            [
                'manzanaOrders' => $manzanaOrders->getKeys(),
                'USER_ID' => $userId,
                'page' => $nav->getCurrentPage()
            ],
            $cachePath
        );
        if ($startResultCacheRes) {
        */
        $activeOrders = $closedOrders = new ArrayCollection();
        try {
            $this->arResult['ACTIVE_ORDERS'] = $activeOrders = $this->orderService->getActiveSiteOrders();
            /** @noinspection PhpUndefinedVariableInspection */
            $allClosedOrders = $this->orderService->mergeAllClosedOrders($this->orderService->getClosedSiteOrders()->toArray(),
                $manzanaOrders->toArray());
            /** Сортировка по дате и статусу общих заказов */
            $allClosedOrdersList = $allClosedOrders->toArray();
            usort($allClosedOrdersList, ['FourPawsPersonalCabinetOrdersComponent', 'sortByStatusAndDate']);

            /** имитация постранички */
            $nav->setRecordCount($allClosedOrders->count());
            $this->arResult['CLOSED_ORDERS'] = $closedOrders = new ArrayCollection(array_slice($allClosedOrdersList,
                $nav->getOffset(), $nav->getPageSize(), true));
            $this->arResult['NAV'] = $nav;
        } catch (NotAuthorizedException $e) {
            /*
            $this->abortResultCache();
            */
            /** запрашиваем авторизацию */
            \define('NEED_AUTH', true);
            return null;
        } catch (\Exception $e) {
            /*
            $this->abortResultCache();
            */
            $logger = LoggerFactory::create('my_orders');
            $logger->error('error - ' . $e->getMessage());
            /** Показываем пустую страницу с заказами */
        }

        if (!$activeOrders->isEmpty() || !$closedOrders->isEmpty()) {
            $storeService = App::getInstance()->getContainer()->get('store.service');
            $this->arResult['METRO'] = new ArrayCollection($storeService->getMetroInfo());
        }

        /*
            TaggedCacheHelper::addManagedCacheTags([
                'personal:orders',
                'personal:orders:'. $userId,
                'order:'. $userId
            ]);
            //$this->setResultCacheKeys(['ACTIVE_ORDERS', 'CLOSED_ORDERS']);
            $this->endResultCache();
        }
        */

        $this->includeComponentTemplate();

        return $this;
    }

    /**
     * @param Order $item1
     * @param Order $item2
     *
     * @return int
     */
    public function sortByStatusAndDate(Order $item1, Order $item2): int
    {
        if ($item1->getStatusSort() === $item2->getStatusSort()) {
            if ($item1->getDateInsert() > $item2->getDateInsert()) {
                return 1;
            }

            if ($item1->getDateInsert() < $item2->getDateInsert()) {
                return -1;
            }

            return 0;
        }

        if ($item1->getStatusSort() > $item2->getStatusSort()) {
            return 1;
        }

        return -1;
    }

    public function getCurrentUserService()
    {
        return $this->currentUserProvider;
    }

    /**
     * @param OrderItem $item
     * @param int       $percent
     * @param int       $precision
     *
     * @return string
     */
    public function getItemBonus(OrderItem $item, int $percent, int $precision = 2): string
    {
        $bonusText = '';
        $bonus = \round($item->getPrice() * $item->getQuantity() * $percent / 100, $precision);
        if ($bonus <= 0) {
            return $bonusText;
        }

        if ($precision > 0) {
            $bonus = \round($bonus, $precision, \PHP_ROUND_HALF_DOWN);
            $floorBonus = \floor($bonus);
        } else {
            $floorBonus = $bonus = floor($bonus);
        }

        $div = ($bonus - $floorBonus) * 100;

        return \sprintf(
            '+ %s %s',
            WordHelper::numberFormat($bonus, $precision),
            WordHelper::declension($div ?: $floorBonus, ['бонус', 'бонуса', 'бонусов'])
        );
    }

    /**
     * Взято из компонента списка заказов Битиркса common/bitrix/components/bitrix/sale.personal.order.list/class.php
     *
     * @param int          $id Order id
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
