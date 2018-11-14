<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Offer;

/**
 * Class OfferCollection
 *
 * @package FourPaws\Catalog\Collection
 */
class OfferCollection extends IblockElementCollection
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): \Generator
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        if($this->getCdbResult() instanceof \CIBlockResult){
            while ($fields = $this->getCdbResult()->GetNextElement()) {
                $result = $fields->GetFields();
                $result['PROPERTIES'] = $fields->GetProperties();
                foreach ($result['PROPERTIES'] as $key => &$arProp) {
                    $result['PROPERTY_' . $key . '_VALUE'] = $arProp['VALUE'];
                    $result['~PROPERTY_' . $key . '_VALUE'] = $arProp['VALUE'];
                    if (isset($arProp['PROPERTY_VALUE_ID'])) {
                        $result['PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                        $result['~PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                    }
                }
                unset($result['PROPERTIES']);
                yield new Offer($result);
            }
        } elseif($this->getCdbResult() instanceof \CDBResult) {
            while ($fields = $this->getCdbResult()->GetNext()) {
                yield new Offer($fields);
            }
        }
    }
}
