<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\StoreBundle\Entity\Store;
use Bitrix\Sale\BasketItem;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderShopListComponent extends \CBitrixComponent
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

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->prepareResult();

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    protected function prepareResult()
    {
        $serviceContainer = Application::getInstance()->getContainer();

        $storage = $this->orderService->getStorage();
        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);
        /** @var StoreService $storeService */
        $storeService = $serviceContainer->get('store.service');

        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries();
        foreach ($deliveries as $delivery) {
            if ($delivery->getData()['DELIVERY_CODE'] === DeliveryService::INNER_PICKUP_CODE) {
                $pickupDelivery = $delivery;
            }
        }

        if (!$pickupDelivery) {
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

        $shops = $storeService->getByLocation($storage->getCityCode(), StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            throw new \RuntimeException('No shops found');
        }

        /** @var StockResultCollection $stockResult */
        $stockResult = $pickupDelivery->getData()['STOCK_RESULT'];
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
            $resultByShop[$shop->getXmlId()]['DELAYED_AMOUNT'] = 0;
            if (!$delayed->isEmpty()) {
                $resultByShop[$shop->getXmlId()]['DELAYED_AMOUNT'] = $delayed->getAmount();
                $shopsPartial[] = $shop;
            } else {
                $shopsFull[] = $shop;
            }

            $available = $stockResultByShop->getAvailable();
            $resultByShop[$shop->getXmlId()]['AVAILABLE_AMOUNT'] = 0;
            if (!$available->isEmpty()) {
                $resultByShop[$shop->getXmlId()]['AVAILABLE_AMOUNT'] = $available->getAmount();
            }
            $resultByShop[$shop->getXmlId()]['STOCK_RESULT'] = $stockResultByShop;
        }

        $this->arResult = [
            'PICKUP_DELIVERY'      => $pickupDelivery,
            'METRO'                => $storeService->getMetroInfo(),
            'SHOPS'                => $shops,
            'SHOPS_PARTIAL'        => $shopsPartial,
            'SHOPS_FULL'           => $shopsFull,
            'BASKET'               => $basket,
            'STOCK_RESULT_BY_SHOP' => $resultByShop,
            'OFFERS'               => $offers,
        ];
    }
}
