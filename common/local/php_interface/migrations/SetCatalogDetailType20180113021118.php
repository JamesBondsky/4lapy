<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\Query;
use FourPaws\Enum\IblockCode;

class SetCatalogDetailType20180113021118 extends SprintMigrationBase
{
    
    protected $description = 'Установка типа элементов каталога в html';
    
    public function up() {
        $ob = new \CIBlockElement();
        $iblockId = (new HelperManager())->Iblock()->getIblockId(IblockCode::PRODUCTS);
        
        $els =
            (new Query(ElementTable::getEntity()))->setFilter(['IBLOCK_ID' => $iblockId])
                                                  ->setSelect(['ID'])
                                                  ->exec()
                                                  ->fetchAll();
        
        foreach ($els as $el) {
            $ob->Update($el['ID'], ['DETAIL_TEXT_TYPE' => 'html']);
        }
        
    }
    
    public function down() {
        /**
         * Bug fix. We can't down.
         */
    }
    
}
