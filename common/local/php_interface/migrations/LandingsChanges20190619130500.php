<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class LandingsChanges20190619130500 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Изменяет инфоблок \"победителей акций\" для лендингов';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->getIblockId('action_winners', 'grandin');

        $grandinSection = $helper->Iblock()->addSection($iblockId, [
            'NAME' => 'mealfeel',
            'CODE' => 'MEALFEEL',
        ]);
    }

    public function down(){

    }
}
