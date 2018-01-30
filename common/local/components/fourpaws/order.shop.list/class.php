<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\StoreBundle\Entity\Store;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderShopListComponent extends FourPawsShopListComponent
{
    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct($component = null)
    {
        $this->orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        parent::__construct($component);
    }

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws \RuntimeException
     */
    protected function prepareResult()
    {
        parent::prepareResult();

        $serviceContainer = Application::getInstance()->getContainer();

        $storage = $this->orderService->getStorage();
        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);

        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries();
        foreach ($deliveries as $delivery) {
            if ($deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        if (null === $pickupDelivery) {
            return;
        }

        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return;
        }

        $offerIds = [];
        $basket = $basketService->getBasket()->getOrderableItems();
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            if (!$item->getProductId()) {
                continue;
            }

            $offerIds[] = $item->getProductId();
        }

        if ($basket->isEmpty() || empty($offerIds)) {
            throw new \RuntimeException('Basket is empty');
        }

        $offers = (new OfferQuery())->withFilterParameter('ID', $offerIds)->exec();
        if ($offers->isEmpty()) {
            throw new \RuntimeException('Offers not found');
        }

        $shops = $this->storeService->getByLocation($storage->getCityCode(), StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            throw new \RuntimeException('No shops found');
        }

        $resultByShop = [];
        $shopsPartial = [];
        $shopsFull = [];

        /** @var Store $shop */
        foreach ($shops as $shop) {
            $stockResultByShop = $stockResult->filterByStore($shop);
            if (!$stockResultByShop->getUnavailable()->isEmpty()) {
                continue;
            }

            $resultByShop[$shop->getXmlId()] = [];

            $delayed = $stockResultByShop->getDelayed();
            $available = $stockResultByShop->getAvailable();

            $resultByShop[$shop->getXmlId()]['DELAYED_AMOUNT'] = 0;
            if (!$delayed->isEmpty()) {
                $resultByShop[$shop->getXmlId()]['DELAYED_AMOUNT'] = $delayed->getAmount();
                $shopsPartial[] = $shop;
            } else {
                $shopsFull[] = $shop;
            }

            $fullResult = clone $pickupDelivery;
            $partialResult = clone $pickupDelivery;
            $updateDeliveryDate = function (CalculationResult $pickup, \DateTime $deliveryDate) {
                $date = new \DateTime();
                if ($deliveryDate->format('z') !== $date->format('z')) {
                    $pickup->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);
                    $pickup->setPeriodFrom($deliveryDate->format('z') - $date->format('z'));
                } else {
                    $pickup->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);
                    $pickup->setPeriodFrom($deliveryDate->format('G') - $date->format('G'));
                }
            };

            $updateDeliveryDate($partialResult, $available->getDeliveryDate());
            $updateDeliveryDate($fullResult, $stockResultByShop->getDeliveryDate());
            $resultByShop[$shop->getXmlId()]['PARTIAL_RESULT'] = $partialResult;
            $resultByShop[$shop->getXmlId()]['FULL_RESULT'] = $fullResult;

            $resultByShop[$shop->getXmlId()]['AVAILABLE_AMOUNT'] = 0;
            if (!$available->isEmpty()) {
                $resultByShop[$shop->getXmlId()]['AVAILABLE_AMOUNT'] = $available->getAmount();
            }
            $resultByShop[$shop->getXmlId()]['STOCK_RESULT'] = $stockResultByShop;
        }
        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $storeListUrlRoute = $routeCollection->get('fourpaws_sale_ajax_order_storesearch');

        $this->arResult['STORE_LIST_URL'] = $storeListUrlRoute ? $storeListUrlRoute->getPath() : '';
        $this->arResult['SHOPS'] = $shops;
        $this->arResult['SHOPS_PARTIAL'] = $shopsPartial;
        $this->arResult['SHOPS_FULL'] = $shopsFull;
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STOCK_RESULT_BY_SHOP'] = $resultByShop;
        $this->arResult['OFFERS'] = $offers;
    }

    protected function getStoreList(array $filter, array $order): array
    {
        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return [];
        }

        $storeRepository = $this->storeService->getRepository();
        $filter = array_merge($filter, $this->storeService->getTypeFilter($this->storeService::TYPE_SHOP));
        $shops = $storeRepository->findBy($filter, $order);

        foreach ($shops as $i => $shop) {
            $stockResultByShop = $stockResult->filterByStore($shop);
            if (!$stockResultByShop->getUnavailable()->isEmpty()) {
                $shops->remove($i);
            }
        }

        return $shops->toArray();
    }

    protected function getStockResult()
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries();
        foreach ($deliveries as $delivery) {
            if ($deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        if (!$pickupDelivery) {
            return false;
        }

        return $pickupDelivery->getData()['STOCK_RESULT'];
    }
}
