<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogUniqueCode20171220155233 extends SprintMigrationBase
{
    protected $description = 'Catalog Section Unique Code';

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

        $newFields = [];
        $newFields['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE'] = 'Y';

        return $iblockHelper->updateIblockFields($iblockId, $newFields);
    }

    public function down()
    {
        $helper = new HelperManager();

        //your code ...
    }
}
