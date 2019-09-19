<?php

namespace FourPaws\Catalog\Model\Filter;

use Bitrix\Catalog\StoreTable;
use Bitrix\Sale\Delivery\CalculationResult;
use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Variant;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Handler\DeliveryHandlerBase;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Stock;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

/**
 * Class DeliveryAvailabilityFilter
 * @package FourPaws\Catalog\Model\Filter
 */
class DeliveryAvailabilityFilter extends FilterBase
{

    /**
     * @var StoreCollection
     */
    private $suppliers;

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'DeliveryAvailability';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'availableStores';
    }

    /**
     * @return VariantCollection
     * @throws ApplicationCreateException
     */
    public function doGetAllVariants(): VariantCollection
    {
        $result = new VariantCollection();
        $result->add((new Variant())
            ->withName('Доставка')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_DELIVERY));
        $result->add((new Variant())
            ->withName('Самовывоз')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_PICKUP));
        $result->add((new Variant())
            ->withName('Под заказ')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_BY_REQUEST));
        return $result;
    }


    /**
     * @return AbstractQuery
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getFilterRule(): AbstractQuery
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

        $storeIds = [];
        $delivery = null;
        $pickup = null;
        $deliveryZone = $deliveryService->getCurrentDeliveryZone();
        $deliveries = $deliveryService->getByLocation();

        /** @var CalculationResultInterface $calculationResult */
        foreach($deliveries as $calculationResult){
            if(!$delivery && $deliveryService->isDelivery($calculationResult)){
                $delivery = $calculationResult;
            }
            if(!$pickup && $deliveryService->isPickup($calculationResult)){
                $pickup = $calculationResult;
            }
        }

        $checkedVariants = $this->getCheckedVariants();
        $deliveryVariant = function($index, Variant $variant){
            return $variant->getValue() == Product::AVAILABILITY_DELIVERY;
        };
        $pickupVariant = function($index, Variant $variant){
            return $variant->getValue() == Product::AVAILABILITY_PICKUP;
        };
        $requestVariant = function($index, Variant $variant){
            return $variant->getValue() == Product::AVAILABILITY_BY_REQUEST;
        };

        if($checkedVariants->exists($deliveryVariant) && $delivery){
            $stores = DeliveryHandlerBase::getAvailableStores($delivery->getDeliveryCode(), $deliveryZone);
            /** @var Store $store */
            foreach ($stores as $store){
                $storeIds[] = $store->getXmlId();
            }
        }
        if($checkedVariants->exists($pickupVariant) && $pickup){
            $stores = DeliveryHandlerBase::getAvailableStores($pickup->getDeliveryCode(), $deliveryZone);
            /** @var Store $stock */
            foreach ($stores as $store){
                $storeIds[] = $store->getXmlId();
            }
        }
        if($checkedVariants->exists($deliveryVariant) || $checkedVariants->exists($requestVariant)){
            $stores = $this->getSuppliers();
            /** @var Store $stock */
            foreach ($stores as $store){
                $storeIds[] = $store->getXmlId();
            }
        }

        $storeIds = array_filter($storeIds);

        $queryBuilder = new QueryBuilder();
        $termsQuery = $queryBuilder->query()->terms('availableStores', $storeIds);

        return $termsQuery;
    }



    /**
     * @param string $aggName
     * @param array $aggResult
     */
    public function collapse(string $aggName, array $aggResult): void
    {
        // фильтр доступности должен быть виден всегда
        return;
    }

    /**
     * @return \FourPaws\StoreBundle\Collection\StoreCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getSuppliers()
    {
        if(null === $this->suppliers){
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $this->suppliers = $storeService->getStores(StoreService::TYPE_SUPPLIER);
        }

        return $this->suppliers;
    }

}
