<?php

namespace Articul\Landing\Orm;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Class GroomingTable
 * @package Articul\Landing\Orm
 */
class GroomingTable extends ElementTable
{
    /**
     * @return array
     */
    public static function getMap() {
        $arFields        = parent::getMap();
        $arFields['UTS'] = new ReferenceField(
            'UTS',
            '\Articul\Landing\Orm\UtsGroomingTable',
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
        );
        
        return $arFields;
    }
}
