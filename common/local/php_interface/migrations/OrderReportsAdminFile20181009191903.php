<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class OrderReportsAdminFile20181009191903 extends SprintMigrationBase
{
    protected $description = 'Копирование админ-страницы генерации отчетов по заказам в папку bitrix';

    public function up() {
        copy(
            $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/migration_sources/fourpaws_retail_rocket_orders_report.php',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/fourpaws_retail_rocket_orders_report.php'
        );
    }

    public function down()
    {
    }
}
