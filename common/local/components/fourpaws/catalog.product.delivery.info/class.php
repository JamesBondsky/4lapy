<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StockCollection;
use Doctrine\Common\Collections\ArrayCollection;

CBitrixComponent::includeComponentClass('fourpaws:city.delivery.info');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCatalogProductDeliveryInfoComponent extends FourPawsCityDeliveryInfoComponent
{
    /**
     * @var StoreService
     */
    protected $storeService;

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
    }

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['OFFER']) && !empty($params['OFFER_ID'])) {
            $params['OFFER'] = $this->getOffer($params['OFFER_ID']);
        }

        $params['OFFER'] = $params['OFFER'] instanceof Offer ? $params['OFFER'] : null;
        $params['STOCKS'] = $params['STOCKS'] instanceof StockCollection ? $params['STOCKS'] : null;

        return parent::onPrepareComponentParams($params);
    }

    protected function prepareResult()
    {
        if (!$this->arParams['OFFER']) {
            throw new \InvalidArgumentException('Invalid component parameters');
        }
        parent::prepareResult();

        if (isset($this->arResult['DEFAULT']['PICKUP']) &&
            $this->arResult['DEFAULT']['PICKUP']['CODE'] == DeliveryService::INNER_PICKUP_CODE
        ) {
            $this->arResult['DEFAULT']['PICKUP']['SHOP_COUNT'] = $this->getShopCount(
                $this->arResult['DEFAULT']['LOCATION']['CODE']
            );
        }

        if (isset($this->arResult['CURRENT']['PICKUP']) &&
            $this->arResult['CURRENT']['PICKUP']['CODE'] == DeliveryService::INNER_PICKUP_CODE
        ) {
            if ($this->arResult['CURRENT']['LOCATION']['CODE'] == $this->arResult['DEFAULT']['LOCATION']['CODE']) {
                $this->arResult['CURRENT']['PICKUP']['SHOP_COUNT'] = $this->arResult['DEFAULT']['PICKUP']['SHOP_COUNT'];
            } else {
                $stores = $this->storeService->getByLocation(
                    $this->arResult['CURRENT']['LOCATION']['CODE'],
                    StoreService::TYPE_SHOP
                );

                if (!$this->arParams['STOCKS']) {
                    /** @var Offer $offer */
                    $offer = $this->arParams['OFFER'];
                    $this->storeService->getStocks(new ArrayCollection([$offer]), $stores);
                    $this->arParams['STOCKS'] = $offer->getStocks();
                }
                $this->arResult['CURRENT']['PICKUP']['SHOP_COUNT'] = $this->getShopCount(
                    $this->arResult['CURRENT']['LOCATION']['CODE']
                );
            }
        }
    }

    /**
     * @param string $locationCode
     * @param array $possibleDeliveryCodes
     *
     * @return null|CalculationResult[]
     */
    protected function getDeliveries(string $locationCode, array $possibleDeliveryCodes = [])
    {
        return $this->deliveryService->getByProduct(
            $this->arParams['OFFER'],
            $locationCode,
            $possibleDeliveryCodes
        );
    }

    /**
     * @param string $code
     * @param int $iblockId
     *
     * @return null|Offer
     */
    protected function getOffer(int $id)
    {
        return (new OfferQuery($id))
            ->withFilterParameter('ID', $id)
            ->exec()
            ->first();
    }

    protected function getShopCount(string $locationCode)
    {
        $stores = $this->storeService->getByLocation(
            $locationCode,
            StoreService::TYPE_SHOP
        );

        /** @var Offer $offer */
        $offer = $this->arParams['OFFER'];

        $this->storeService->getStocks(new ArrayCollection([$offer]), $stores);

        return $offer->getStocks()->count();
    }
}
