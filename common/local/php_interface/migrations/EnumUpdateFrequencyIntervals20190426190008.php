<?php

namespace Sprint\Migration;


use CUserFieldEnum;

class EnumUpdateFrequencyIntervals20190426190008 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Заменяет интервалы \"раз в месяц\", \"раз в два месяца\"";

    public function up(){
        $helper = new HelperManager();

        $entityId = $helper->Hlblock()->getHlblockId('OrderSubscribe');
        $fieldId = $helper->UserTypeEntity()->getUserTypeEntity("HLBLOCK_".$entityId, 'UF_FREQUENCY')['ID'];

        CUserFieldEnum::DeleteFieldEnum($fieldId);

        (new \CUserFieldEnum())->SetEnumValues(
            $fieldId,
            [
                'n1' => [
                    'XML_ID' => 'WEEK_1',
                    'VALUE' => 'Раз в неделю',
                    'SORT' => 100,
                ],
                'n2' => [
                    'XML_ID' => 'WEEK_2',
                    'VALUE' => 'Раз в две недели',
                    'SORT' => 200,
                ],
                'n3' => [
                    'XML_ID' => 'WEEK_3',
                    'VALUE' => 'Раз в три недели',
                    'SORT' => 300,
                ],
                'n4' => [
                    'XML_ID' => 'WEEK_4',
                    'VALUE' => 'Раз в четыре недели',
                    'SORT' => 400,
                ],
                'n5' => [
                    'XML_ID' => 'WEEK_5',
                    'VALUE' => 'Раз в пять недель',
                    'SORT' => 500,
                ],
                'n6' => [
                    'XML_ID' => 'WEEK_6',
                    'VALUE' => 'Раз в шесть недель',
                    'SORT' => 600,
                ],
            ]
        );

    }

    public function down(){
        $helper = new HelperManager();

        //your code ...

    }

}
