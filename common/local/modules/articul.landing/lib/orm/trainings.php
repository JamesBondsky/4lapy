<?php

namespace Articul\Landing\Orm;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

\Bitrix\Main\Loader::includeModule('iblock');

class TrainingsTable extends ElementTable
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
            '\Articul\Landing\Orm\UtsTrainingsTable',
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
        );
        
        return $arFields;
    }
}
