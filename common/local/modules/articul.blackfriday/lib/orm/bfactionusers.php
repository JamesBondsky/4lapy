<?php

namespace Articul\BlackFriday\Orm;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Class BFSectionsTable
 * @package Articul\Landing\Orm
 */
class BFActionUsersTable extends ElementTable
{
    /**
     * @return array
     */
    public static function getMap() {
        $arFields        = parent::getMap();
        $arFields['UTS'] = new ReferenceField(
            'UTS',
            '\Articul\BlackFriday\Orm\UtsBFActionUsersTable',
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
        );
        
        return $arFields;
    }
}
