<?php

namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class ClosingSizeTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_hlbd_clothing_size_selection';
    }
    
    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            'ID'           => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),
            'UF_CODE'      => new StringField('UF_CODE', []),
            'UF_CHEST_MIN' => new StringField('UF_CHEST_MIN', []),
            'UF_CHEST_MAX' => new StringField('UF_CHEST_MAX', []),
            'UF_NECK_MIN'  => new StringField('UF_NECK_MIN', []),
            'UF_NECK_MAX'  => new StringField('UF_NECK_MAX', []),
            'UF_BACK_MIN'  => new StringField('UF_BACK_MIN', []),
            'UF_BACK_MAX'  => new StringField('UF_BACK_MAX', []),
        ];
    }
}
