<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddHrefToRootMenu20180604112923 extends SprintMigrationBase
{

    protected $description = 'добавление ссылок к меню';

    public function up()
    {
        $helper = new HelperManager();

        try {
            $iblockId = IblockUtils::getIblockId(IblockType::MENU, IblockCode::MAIN_MENU);
        } catch (IblockNotFoundException $e) {
            $iblockId = 0;
        }
        if ($iblockId > 0) {
            $iblockHelper = $helper->Iblock();
            $sections = $iblockHelper->getSections($iblockId, ['=CODE' => 'pet', '=DEPTH_LEVEL' => 1]);
            if (\is_array($sections) && \count($sections) > 0) {
                $sectId = (int)reset($sections)['ID'];
                if ($sectId > 0) {
                    $iblockHelper->updateSection($sectId, ['UF_HREF' => '/catalog/']);
                }
            }
            $sections = $iblockHelper->getSections($iblockId, ['=NAME' => 'По бренду', '=DEPTH_LEVEL' => 1]);
            if (\is_array($sections) && \count($sections) > 0) {
                $sectId = (int)reset($sections)['ID'];
                if ($sectId > 0) {
                    $iblockHelper->updateSection($sectId, ['CODE' => 'brands', 'UF_HREF' => '/brand/']);
                }
            }
        }
    }
}
