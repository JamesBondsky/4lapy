<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;
use Bitrix\Sale\Internals\DeliveryHandlerTable;

class RenameDeliveryName20180417123200 extends SprintMigrationBase
{

    protected $description = 'Переименование службы доставки';

    public function up()
    {
        $deliveryName = 'Доставка "Четыре лапы"';
        $deliveryNewName = 'Доставка "Четыре Лапы"';
        $deliveryId = (int)DeliveryTable::query()->setSelect(['ID'])->setFilter(['NAME' => $deliveryName])->exec()->fetch()['ID'];
        if ($deliveryId > 0) {
            DeliveryTable::update($deliveryId, ['NAME' => $deliveryNewName]);
        }

        $deliveryName = 'Самовывоз из магазина "Четыре лапы"';
        $deliveryNewName = 'Самовывоз из магазина "Четыре Лапы"';
        $deliveryId = (int)DeliveryTable::query()->setSelect(['ID'])->setFilter(['NAME' => $deliveryName])->exec()->fetch()['ID'];
        if ($deliveryId > 0) {
            DeliveryTable::update($deliveryId, ['NAME' => $deliveryNewName]);
        }
    }
}
