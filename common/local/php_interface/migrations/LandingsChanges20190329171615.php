<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class LandingsChanges20190329171615 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Изменяет инфоблок \"Заявки\" для лендингов';

    protected $requestSections = [
        'grandin_requests' => [
            'NAME' => 'Заявки Grandin',
            'CODE' => 'grandin_requests'
        ],
        'royal_canin_requests' => [
            'NAME' => 'Заявки Royal-canin',
            'CODE' => 'royal_canin_requests'
        ]
    ];

    public function up()
    {
        $res = true;
        try {
            $helper = new HelperManager();

            $helper->Iblock()->updateIblockType('grandin', [
                'SECTIONS' => 'Y',
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Лендинги',
                        'SECTION_NAME' => 'Разделы',
                        'ELEMENT_NAME' => 'Элементы'
                    ]
                ]
            ]);

            $requestIblockId = $helper->Iblock()->getIblockId('grandin_request', 'grandin');

            $iblockSectionIds = [];
            foreach ($this->requestSections as $requestSection) {
                $iblockSectionIds[$requestSection['CODE']] = $helper->Iblock()->addSectionIfNotExists($requestIblockId, $requestSection);
            }

            $requestElements = $helper->Iblock()->getElements($requestIblockId);
            foreach ($requestElements as $element) {
                $helper->Iblock()->updateElement(
                    $element['ID'],
                    [
                        'IBLOCK_SECTION_ID' => $iblockSectionIds['grandin_requests']
                    ]
                );
            }
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
