<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Service\StoreService;

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
     * @throws Exception
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

        $basket = $basketService->getBasket()->getOrderableItems();
        if ($basket->isEmpty()) {
            throw new \RuntimeException('Basket is empty');
        }

        $shops = $storeService->getByLocation($storage->getCityCode(), StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            throw new \RuntimeException('No shops found');
        }
    }
}
