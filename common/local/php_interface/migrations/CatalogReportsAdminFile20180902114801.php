<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class CatalogReportsAdminFile20180902114801 extends SprintMigrationBase
{
    protected $description = 'Копирование админ-страницы генерации отчетов в папку bitrix';

    public function up() {
        copy(
            $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/migration_sources/fourpaws_products_report.php',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/fourpaws_products_report.php'
        );
    }

    public function down()
    {
    }
}
