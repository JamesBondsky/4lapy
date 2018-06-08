<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ReplaceUrls20180608191946 extends SprintMigrationBase
{

    protected $description = 'замена url новостей, акций, статей';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES);
        $replacement = 'services/';
        $iblockFields = $helper->Iblock()->getIblockFields($iblockId);
        $helper->Iblock()->updateIblockFields($iblockId, [
            'LIST_PAGE_URL'      => str_replace($replacement, '', $iblockFields['LIST_PAGE_URL']),
            'DETAIL_PAGE_URL'    => str_replace($replacement, '', $iblockFields['DETAIL_PAGE_URL']),
            'SECTION_PAGE_URL'   => str_replace($replacement, '', $iblockFields['SECTION_PAGE_URL']),
            'CANONICAL_PAGE_URL' => str_replace($replacement, '', $iblockFields['CANONICAL_PAGE_URL']),
        ]);

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS);
        $replacement = 'services/';
        $iblockFields = $helper->Iblock()->getIblockFields($iblockId);
        $helper->Iblock()->updateIblockFields($iblockId, [
            'LIST_PAGE_URL'      => str_replace($replacement, '', $iblockFields['LIST_PAGE_URL']),
            'DETAIL_PAGE_URL'    => str_replace($replacement, '', $iblockFields['DETAIL_PAGE_URL']),
            'SECTION_PAGE_URL'   => str_replace($replacement, '', $iblockFields['SECTION_PAGE_URL']),
            'CANONICAL_PAGE_URL' => str_replace($replacement, '', $iblockFields['CANONICAL_PAGE_URL']),
        ]);

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);
        $replacement = 'customer/';
        $iblockFields = $helper->Iblock()->getIblockFields($iblockId);
        $helper->Iblock()->updateIblockFields($iblockId, [
            'LIST_PAGE_URL'      => str_replace($replacement, '', $iblockFields['LIST_PAGE_URL']),
            'DETAIL_PAGE_URL'    => str_replace($replacement, '', $iblockFields['DETAIL_PAGE_URL']),
            'SECTION_PAGE_URL'   => str_replace($replacement, '', $iblockFields['SECTION_PAGE_URL']),
            'CANONICAL_PAGE_URL' => str_replace($replacement, '', $iblockFields['CANONICAL_PAGE_URL']),
        ]);
    }
}
