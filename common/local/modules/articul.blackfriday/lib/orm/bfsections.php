<?php

namespace Articul\BlackFriday\Orm;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Class BFSectionsTable
 * @package Articul\Landing\Orm
 */
class BFSectionsTable extends ElementTable
{
    /**
     * @return array
     */
    public static function getMap() {
        $arFields        = parent::getMap();
        $arFields['UTS'] = new ReferenceField(
            'UTS',
            '\Articul\BlackFriday\Orm\UtsBFSectionsTable',
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
        );
        
        return $arFields;
    }
}
