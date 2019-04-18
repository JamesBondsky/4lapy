<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class ChangeOrderPropDostavistaUserCoordsCode20190411165700 extends SprintMigrationBase
{
    protected const PROP_OLD_CODE = 'USER_COORDS_DOSTAVISTA';
    protected const PROP_NEW_CODE = 'USER_COORDS';
    protected const PROP_NEW_NAME = 'Координаты пользователя';

    protected $description = 'Изменение кода свойства заказа "координаты пользователя"';

    public function up()
    {
        $prop = OrderPropsTable::getList(
            [
                'filter' => [
                    'CODE' => static::PROP_OLD_CODE
                ],
            ]
        )->fetch();

        if (!is_array($prop)) {
            return false;
        }

        $updateResult = OrderPropsTable::update(
            $prop['ID'],
            [
                'CODE' => static::PROP_NEW_CODE,
                'NAME' => static::PROP_NEW_NAME
            ]
        );

        if (!$updateResult->isSuccess()) {
            return false;
        }

        return true;
    }

    public function down()
    {

    }
}