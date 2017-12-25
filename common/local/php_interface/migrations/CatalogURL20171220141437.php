<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogURL20171220141437 extends SprintMigrationBase
{
    protected $description = 'Catalog URL';

    public function up()
    {
        $helper = new HelperManager();

        $iblockHelper = $helper->Iblock();

        $iblockFields = $iblockHelper->getIblock(IblockCode::PRODUCTS, IblockType::CATALOG);
        if (!($iblockId = $iblockFields['ID'])) {
            $this->log()->error(sprintf(
                'No such iblock with code: %s and type: %s',
                IblockCode::PRODUCTS,
                IblockType::CATALOG
            ));
            return false;
        }

        $cIblock = new \CIBlock();

        return $cIblock->Update($iblockId, array_merge($iblockFields, [
            'LIST_PAGE_URL'      => '#SITE_DIR#/catalog/',
            'SECTION_PAGE_URL'   => '#SITE_DIR#/catalog/#SECTION_CODE_PATH#/',
            'DETAIL_PAGE_URL'    => '#SITE_DIR#/catalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#.html',
            'CANONICAL_PAGE_URL' => 'https://#SERVER_NAME##SITE_DIR#/catalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#.html',
        ]));
    }

    public function down()
    {
    }
}
