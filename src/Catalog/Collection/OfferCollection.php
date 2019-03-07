<?php

namespace FourPaws\Catalog\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\BitrixOrm\Model\BitrixArrayItemBase;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\PriceQuery;
use FourPaws\Catalog\Collection\PriceCollection;

/**
 * Class OfferCollection
 *
 * @package FourPaws\Catalog\Collection
 */
class OfferCollection extends IblockElementCollection
{
    /**
     * @var PriceCollection
     */
    protected $priceCollection;

    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @inheritdoc
     */
    protected function fetchElement(): \Generator
    {
        $props = (new OfferQuery())->getProperties();
        while ($fields = $this->getCdbResult()->GetNextElement()) {
            $result = $fields->GetFields();
            $result['PROPERTIES'] = $fields->GetProperties();
            foreach ($result['PROPERTIES'] as $key => &$arProp) {
                if (in_array($key, $props)) {
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['VALUE'] == '') {
                        $val = null;
                    } else {
                        $val = $arProp['VALUE'];
                    }
                    $result['PROPERTY_' . $key . '_VALUE'] = $val;
                    $result['~PROPERTY_' . $key . '_VALUE'] = $val;
                    if (isset($arProp['PROPERTY_VALUE_ID'])) {
                        $result['PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                        $result['~PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                    }
                }
            }
            unset($result['PROPERTIES']);
            yield new Offer($result);
        }
    }

    /**
     * Do the initialization logic
     *
     * @return void
     */
    protected function doInitialize()
    {
        $this->collection = new ArrayCollection();
        foreach ($this->fetchElement() as $element) {
            /**
             * @var BitrixArrayItemBase
             */
            $this->collection->set($element->getId(), $element);
            $this->productIds[] = $element->getId();
        }

        $prices = [];
        $this->priceCollection = (new PriceQuery())->withFilter(['=PRODUCT_ID' => $this->productIds])->exec();
        foreach($this->priceCollection as $price){
            if(!$prices[$price->getProductId()] instanceof ArrayCollection){
                $prices[$price->getProductId()] = new ArrayCollection();
            }
            $prices[$price->getProductId()][$price->getId()] = $price;
        }

        foreach ($prices as $productId => $arPrices){
            $this->collection->get($productId)->setPrices($arPrices);
        }
    }

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @param array $productIds
     */
    public function setProductIds(array $productIds): OfferCollection
    {
        $this->productIds = $productIds;
        return $this;
    }
}
