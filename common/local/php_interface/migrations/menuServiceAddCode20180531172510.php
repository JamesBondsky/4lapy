<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class menuServiceAddCode20180531172510 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = 'установка кода для меню ';

    public function up(){
        $helper = new HelperManager();
        $sectId = (int)reset($helper->Iblock()->getSections(IblockUtils::getIblockId(IblockType::MENU, IblockCode::MAIN_MENU), ['=NAME' => 'Сервисы']))['ID'];
        if($sectId > 0){
            $helper->Iblock()->updateSection($sectId, ['CODE' => 'services']);
        }
    }
}
