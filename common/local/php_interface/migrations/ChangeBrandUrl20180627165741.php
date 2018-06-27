<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ChangeBrandUrl20180627165741 extends SprintMigrationBase
{

    protected $description = 'замена ссылки в брендах';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);
        $helper->Iblock()->updateIblockFields($iblockId,
            [
                'DETAIL_PAGE_URL'    => '/brands/#CODE#/',
                'CANONICAL_PAGE_URL' => 'https://#SERVER_NAME#/brands/#CODE#/',
            ]);

    }
}