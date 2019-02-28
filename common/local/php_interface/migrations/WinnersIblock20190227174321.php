<?php

namespace Sprint\Migration;


class WinnersIblock20190227174321 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создание инфоблока для победителей в акции";

    public function up(){
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblock([
            'IBLOCK_TYPE_ID' => 'grandin',
            'NAME' => 'Победители акций',
            'CODE' => 'action_winners'
        ]);
        
        $helper->Iblock()->addProperty($iblockId, [
            'NAME' => 'Номер телефона',
            'CODE' => 'PHONE',
            'PROPERTY_TYPE' => 'S',
            'IS_REQUIRED' => 'Y'
        ]);
        
        $grandinSection = $helper->Iblock()->addSection($iblockId, [
            'NAME' => 'Grandin',
            'CODE' => 'GRANDIN',
        ]);
        $first = $helper->Iblock()->addSection($iblockId, [
            'NAME' => '8.02',
            'SORT' => 10,
            'IBLOCK_SECTION_ID' => $grandinSection
        ]);
        
        $second = $helper->Iblock()->addSection($iblockId, [
            'NAME' => '15.02',
            'SORT' => 20,
            'IBLOCK_SECTION_ID' => $grandinSection
        ]);
        
        $third = $helper->Iblock()->addSection($iblockId, [
            'NAME' => '22.02',
            'SORT' => 30,
            'IBLOCK_SECTION_ID' => $grandinSection
        ]);
        
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Строгонова Светлана',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 10,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****762'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Сериков Сергей',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 20,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****654'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Москва Алина',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 30,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****334'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Сергеева Светлана',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 40,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****625'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Прокофьева светлана',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 50,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****311'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Ляпина Татьяна',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 60,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****967'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Мелентьева Ксения',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 70,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****855'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'гиззатуллина окасана',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 80,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****177'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Казаков Владимир',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 90,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****059'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Petrova Anna',
            'IBLOCK_SECTION_ID' => $first,
            'SORT' => 100,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****307'
            ]
        ]);
    
    
    
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Шувалова Яна',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 10,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****071'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Зубов Вячеслав',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 20,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****083'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Лисина Елена',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 30,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****434'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Шерматова Раиса',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 40,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****985'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Афонасьев Владимир',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 50,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****972'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Конюхова Ольга',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 60,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****803'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Фадеева Марина',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 70,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****628'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Штин Анастасия',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 80,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****004'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Соловьева Инна',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 90,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****237'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'шмелева екатерина',
            'IBLOCK_SECTION_ID' => $third,
            'SORT' => 100,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****998'
            ]
        ]);
    
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Федоткина Татьяна',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 10,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****213'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Новичкова Юлия',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 20,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****861'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Павлихин Артем',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 30,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****322'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Григорчук Илья',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 40,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****463'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Мещеряков Олег',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 50,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****741'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Сафронова Альбина',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 60,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****694'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Ионова Наталья',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 70,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****419'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Писарева Елена',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 80,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****687'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Александрова Елена',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 90,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****712'
            ]
        ]);
        $helper->Iblock()->addElement($iblockId, [
            'NAME' => 'Орехова Анна',
            'IBLOCK_SECTION_ID' => $second,
            'SORT' => 100,
            'PROPERTY_VALUES' => [
                'PHONE' => '*(***)****377'
            ]
        ]);
    }

    public function down(){
        $helper = new HelperManager();

        $helper->Iblock()->deleteIblock($helper->Iblock()->getIblockId('action_winners', 'grandin'));

    }

}
