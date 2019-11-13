<?php

namespace Articul\Landing\Orm;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

\Bitrix\Main\Loader::includeModule('iblock');

class LectionsTable extends ElementTable
{
    /**
     * @return array
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getMap() {
        $arFields        = parent::getMap();
        $arFields['UTS'] = new ReferenceField(
            'UTS',
            '\Articul\Landing\Orm\UtsLectionsTable',
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
        );
        
        return $arFields;
    }
}
