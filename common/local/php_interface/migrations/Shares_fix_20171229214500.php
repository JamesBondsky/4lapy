<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Shares_fix_20171229214500 extends SprintMigrationBase
{
    const IBLOCK_TYPE = 'publications';
    const IBLOCK_CODE = 'shares';

    protected $description = 'Модификация инфоблока акций';

    /**
     * Add shares iblock
     */
    public function up()
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return false;
        }

        $obHelperManager = new HelperManager();
        $iIBlockId = $obHelperManager->Iblock()->getIblockId(static::IBLOCK_CODE, static::IBLOCK_TYPE);
        if (!$iIBlockId) {
            return false;
        }

        $obIBlock = new \CIBlock();
        $obIBlock->Update(
            $iIBlockId,
            array(
                'LIST_PAGE_URL' => '#SITE_DIR#customer/shares/',
                'SECTION_PAGE_URL' => '#SITE_DIR#customer/shares/',
                'DETAIL_PAGE_URL' => '#SITE_DIR#customer/shares/#ELEMENT_CODE#/',
            )
        );

        return true;
    }
    
    /**
     * Remove shares iblock
     */
    public function down()
    {
    }
}
