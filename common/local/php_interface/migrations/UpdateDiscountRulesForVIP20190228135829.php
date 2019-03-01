<?php

namespace Sprint\Migration;

use Bitrix\Main\GroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use Bitrix\Sale\Internals\DiscountGroupTable;
use FourPaws\Enum\UserGroup;

class UpdateDiscountRulesForVIP20190228135829 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "[ОБЯЗАТЕЛЬНО НАЛИЧИЕ ГРУППЫ \"Правила работы с корзиной\"] Обновляет правила работы с корзиной разделяя их на избранных и неизбранных";

    protected $logFile = '/local/php_interface/migration_sources/updated_discount_rules.txt';

    public function up(){
        $this->logFile = $_SERVER['DOCUMENT_ROOT'].$this->logFile;

        $rsGroups = GroupTable::getList([
            'filter' => ['=STRING_ID' => [UserGroup::OPT_CODE, UserGroup::BASKET_RULES]],
            'select' => ['ID', 'CODE' => 'STRING_ID'],
        ]);
        $result = $rsGroups->fetchAll();
        $groups = array_combine(array_column($result, 'CODE'), array_column($result, 'ID'));

        $arDiscounts = [];
        $rsDiscount = DiscountTable::getList([
            'select' => [
                'ID',
                'NAME',
                'GROUP' => 'GROUPS.GROUP_ID'
            ],
            'runtime' => [
                'GROUPS' => [
                    'data_type' => 'Bitrix\Sale\Internals\DiscountGroupTable',
                    'reference' => ['=this.ID' => 'ref.DISCOUNT_ID'],
                    'join_type' => 'left',
                ]
            ]
        ]);

        while($arDiscount = $rsDiscount->fetch()){
            $id = $arDiscount['ID'];

            if($arDiscounts[$id]){
                $arDiscounts[$id]['GROUPS'][] = $arDiscount['GROUP'];
            }
            else{
                $arDiscounts[$id] = [
                    'ID' => $arDiscount['ID'],
                    'NAME' => $arDiscount['NAME'],
                    'GROUPS' => [$arDiscount['GROUP']]
                ];
            }
        }

        $updateDiscounts = [];
        foreach($arDiscounts  as $arDiscount){
            if(array_intersect([2, $groups[UserGroup::BASKET_RULES]], $arDiscount['GROUPS'])){
                continue;
            }
            if(!in_array($groups[UserGroup::OPT_CODE], $arDiscount['GROUPS'])){
                $updateDiscounts[] = [
                    'ID' => $arDiscount['ID'],
                    'NAME' => $arDiscount['NAME'],
                    'GROUPS' => [$groups[UserGroup::BASKET_RULES]],
                    'OLD_GROUPS' => $arDiscount['GROUPS'],
                ];
            }
        }

        foreach ($updateDiscounts as $discount){
            DiscountGroupTable::updateByDiscount($discount['ID'], $discount['GROUPS'], 'Y', true);
        }

        file_put_contents($this->logFile, serialize($updateDiscounts));
    }

    public function down(){
        $this->logFile = $_SERVER['DOCUMENT_ROOT'].$this->logFile;
        $updateDiscounts = unserialize(file_get_contents($this->logFile));

        foreach($updateDiscounts  as $discount){
            DiscountGroupTable::updateByDiscount($discount['ID'], $discount['OLD_GROUPS'], 'Y', true);
        }
    }

}
