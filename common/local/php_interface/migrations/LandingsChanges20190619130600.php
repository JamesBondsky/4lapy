<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class LandingsChanges20190619130600 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Изменяет инфоблок \"Заявки\" для лендингов';

    protected $requestSections = [
        'mealfeel_requests' => [
            'NAME' => 'Заявки Mealfeel',
            'CODE' => 'mealfeel_requests'
        ],
    ];

    public function up()
    {
        $res = true;
        try {
            $helper = new HelperManager();

            $requestIblockId = $helper->Iblock()->getIblockId('grandin_request', 'grandin');

            $iblockSectionIds = [];
            foreach ($this->requestSections as $requestSection) {
                $iblockSectionIds[$requestSection['CODE']] = $helper->Iblock()->addSectionIfNotExists($requestIblockId, $requestSection);
            }

            $requestElements = $helper->Iblock()->getElements($requestIblockId, ['SECTION_CODE' => false]);
            foreach ($requestElements as $element) {
                $helper->Iblock()->updateElement(
                    $element['ID'],
                    [
                        'IBLOCK_SECTION_ID' => $iblockSectionIds['mealfeel_requests']
                    ]
                );
            }
            $winnersIblockId = $helper->Iblock()->getIblockId('action_winners', 'grandin');
            $helper->Iblock()->addSectionIfNotExists($winnersIblockId, ['NAME' => 'Mealfeel', 'CODE' => 'MEALFEEL']);
        } catch (\Exception $e) {
            $res = false;
            dump($e->getCode(), $e->getMessage());
        }

        return $res;
    }

    public function down()
    {

    }
}
