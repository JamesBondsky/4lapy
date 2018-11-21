<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Query\ShareQuery;
use Generator;

/** @noinspection LongInheritanceChainInspection
 *
 * Class ShareCollection
 *
 * @package FourPaws\BitrixOrm\Collection
 */
class ShareCollection extends IblockElementCollection
{
    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        $props = (new ShareQuery())->getProperties();
        if ($this->getCdbResult() instanceof \CIBlockResult) {
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
                yield new Share($result);
            }
        } elseif ($this->getCdbResult() instanceof \CDBResult) {
            while ($fields = $this->getCdbResult()->GetNext()) {
                yield new Share($fields);
            }
        }
    }
}
