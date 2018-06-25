<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

/**
 * Class CatalogProductVatInclude20180625140802
 *
 * @package Sprint\Migration
 */
class CatalogProductVatInclude20180625140802 extends SprintMigrationBase
{
    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Установка значения VAT_INCLUDED для всех товаров';

    /** @noinspection PhpMissingParentCallCommonInspection
     * @return bool
     *
     * @throws SqlQueryException
     */
    public function up(): bool
    {
        Application::getConnection()->query('UPDATE b_catalog_product SET VAT_INCLUDED = \'Y\'');

        return true;
    }
}
