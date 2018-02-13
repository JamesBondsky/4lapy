<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\SaleBundle\Repository\OrderStorage\Table;

class Sale_order_storage_table_create20180212114704 extends SprintMigrationBase
{
    protected $description = 'Создание таблицы для хранения данных по еще не оформленным заказам';

    public function up(): bool
    {
        $entity = Table::getEntity();
        $entity->createDbTable();

        return true;
    }

    public function down(): bool
    {
        $entity = Table::getEntity();
        /** @noinspection SqlNoDataSourceInspection */
        $entity->getConnection()->query('DROP TABLE ' . $entity->getDBTableName());

        return true;
    }
}
