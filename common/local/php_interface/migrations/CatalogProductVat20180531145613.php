<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class CatalogProductVat20180531145613 extends SprintMigrationBase
{
    protected $description = 'Задание значения НДС товарам';

    public function up()
    {
        $vatId = null;
        $vatList = \CCatalogVat::GetListEx();
        while ($vat = $vatList->Fetch()) {
            if ((int)$vat['RATE'] === 18) {
                $vatId = $vat['ID'];
                break;
            }
        }

        if (!$vatId) {
            $this->log()->error('VAT id not found');
            return false;
        }

        Application::getConnection()->query('UPDATE b_catalog_product SET VAT_ID = ' . $vatId);

        return true;
    }

    public function down()
    {

    }
}