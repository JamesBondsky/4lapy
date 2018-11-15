<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Brand;
use Generator;

class BrandCollection extends IblockElementCollection
{
    protected function fetchElement(): Generator
    {
        if ($this->getCdbResult() instanceof \CIBlockResult) {
            while ($fields = $this->getCdbResult()->GetNextElement()) {
                $result = $fields->GetFields();
                $result['PROPERTIES'] = $fields->GetProperties();
                foreach ($result['PROPERTIES'] as $key => &$arProp) {
                    if (in_array($key, $this->getProperties())) {
                        $result['PROPERTY_' . $key . '_VALUE'] = $arProp['VALUE'];
                        $result['~PROPERTY_' . $key . '_VALUE'] = $arProp['VALUE'];
                        if (isset($arProp['PROPERTY_VALUE_ID'])) {
                            $result['PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                            $result['~PROPERTY_' . $key . '_VALUE_ID'] = $arProp['PROPERTY_VALUE_ID'];
                        }
                    }
                }
                unset($result['PROPERTIES']);
                yield new Brand($result);
            }
        } elseif ($this->getCdbResult() instanceof \CDBResult) {
            while ($fields = $this->getCdbResult()->GetNext()) {
                yield new Brand($fields);
            }
        }
    }

}
