<?php

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use Bitrix\Main\Entity;

class Table extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_hlbd_order_storage';
    }

    public static function getUfId()
    {
        return 'ORDER_STORAGE';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField(
                'UF_FUSER_ID',
                [
                    'primary' => true,
                ]
            ),
            new Entity\IntegerField(
                'UF_USER_ID',
                [
                    'required' => false,
                ]
            ),
            new Entity\TextField(
                'UF_DATA',
                [
                    'required' => false,
                    'serialized' => true,
                ]
            ),
        ];
    }
}
