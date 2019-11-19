<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Catalog\Product\Basket;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StockService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Model\Offer;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

CBitrixComponent::includeComponentClass('fourpaws:city.delivery.info');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCatalogProductDeliveryInfoComponent extends FourPawsCityDeliveryInfoComponent
{
    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var StockService
     */
    protected $stockService;

    /**
     * FourPawsCatalogProductDeliveryInfoComponent constructor.
     * @param CBitrixComponent|null $component
     * @throws LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);


        /** @noinspection PhpUnhandledExceptionInspection */
        $serviceContainer = Application::getInstance()->getContainer();
        $this->storeService = $serviceContainer->get('store.service');
        $this->stockService = $serviceContainer->get(StockService::class);
    }

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['OFFER']) && !empty($params['OFFER_ID'])) {
            $params['OFFER'] = $this->getOffer($params['OFFER_ID']);
        }

        $params['OFFER'] = $params['OFFER'] instanceof Offer ? $params['OFFER'] : null;

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'N';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 0;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws CityNotFoundException
     * @throws DeliveryNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function prepareResult(): void
    {
        if (!$this->arParams['OFFER']) {
            throw new \InvalidArgumentException('Invalid component parameters');
        }

        parent::prepareResult();

        if (isset($this->arResult['PICKUP']) &&
            $this->arResult['PICKUP']['CODE'] === DeliveryService::INNER_PICKUP_CODE
        ) {
            $this->arResult['PICKUP']['SHOP_COUNT'] = $this->getShopCount(
                $this->arResult['PICKUP']['RESULT']
            );
        }
    }

    /**
     * @param string $locationCode
     * @param array $possibleDeliveryCodes
     *
     * @return CalculationResultInterface[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws SystemException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     */
    protected function getDeliveries(string $locationCode, array $possibleDeliveryCodes = [])
    {
        $result = [];

        $basket = Bitrix\Sale\Basket::create(SITE_ID);

        $addResult = Basket::addProductToBasketWithPermissions(
            $basket,
            [
                'PRODUCT_ID' => $this->arParams['OFFER']->getId(),
                'QUANTITY' => 1,
                'MODULE' => 'catalog',
                'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
            ],
            [
                'SITE_ID' => SITE_ID,
            ]
        );

        if ($addResult->isSuccess()) {
            $deliveries = $this->deliveryService->getByBasket($basket, $locationCode, $possibleDeliveryCodes);
        } else {
            $deliveries = parent::getDeliveries($locationCode, $possibleDeliveryCodes);
        }

        foreach ($deliveries as $delivery) {
            $delivery->setStockResult(
                $this->deliveryService->getStockResultForOffer($this->arParams['OFFER'], $delivery)
            )->setCurrentDate(new \DateTime());

            if ($delivery->isSuccess()) {
                $result[] = $delivery;
            }
        }

        return $result;
    }

    /**
     * @param int $id
     * @return Offer|null
     * @throws RuntimeException
     */
    protected function getOffer(int $id): ?Offer
    {
        return OfferQuery::getById($id);
    }

    /**
     * @param PickupResultInterface $pickup
     *
     * @return int[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     */
    protected function getShopCount(PickupResultInterface $pickup): array
    {
        $shops = $pickup->getBestShops();
        $pickup = clone $pickup;

        $countTotal = 0;
        $hasToday = false;
        $countFirst = 0;
        $firstDate = null;
        $currentDate = new \DateTime();
        /** @var Store $shop */
        foreach ($shops as $shop) {
            $pickup->setSelectedStore($shop);
            if (!$pickup->isSuccess()) {
                break;
            }

            if (abs($pickup->getDeliveryDate()->getTimestamp() - $currentDate->getTimestamp()) < 2 * 3600) {
                $hasToday = true;
                $countFirst++;
            }

            if (!$hasToday) {
                if (null === $firstDate) {
                    $firstDate = $pickup->getDeliveryDate();
                }
                if ($pickup->getDeliveryDate()->format('z') === $firstDate->format('z')) {
                    $countFirst++;
                }
            }
            $countTotal++;
        }

        return [
            'AVAILABLE' => $countFirst,
            'HAS_TODAY' => $hasToday,
            'TOTAL'     => $countTotal,
        ];
    }
}
