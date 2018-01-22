<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;

class SetCatalogDetailType20180113021118 extends SprintMigrationBase
{
    protected $description = 'Установка типа элементов каталога в html';

    public function up()
    {
        $iblockId = (new HelperManager())->Iblock()->getIblockId(IblockCode::PRODUCTS);
        Application::getConnection()->query("UPDATE b_iblock_element SET DETAIL_TEXT_TYPE='html' WHERE  IBLOCK_ID = " . $iblockId);
    }

    public function down()
    {
        /**
         * Bug fix. We can't down.
         */
    }
}
