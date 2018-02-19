<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class AddPackageDiscountCondition20180219145813
 * @package Sprint\Migration
 */
class AddPackageDiscountCondition20180219145813 extends SprintMigrationBase
{

    protected $description = 'Пресет скидки за упаковку';

    /**
     *
     *
     * @return bool|void
     */
    public function up()
    {
        $helper = new HelperManager();

        \CSaleDiscount::Add([
            'LID' => 's1',
            'NAME' => 'Скидка за упаковку',
            'ACTIVE_FROM' => '',
            'ACTIVE_TO' => '',
            'ACTIVE' => 'Y',
            'SORT' => '100',
            'PRIORITY' => '1',
            'LAST_DISCOUNT' => 'N',
            'LAST_LEVEL_DISCOUNT' => 'N',
            'XML_ID' => 'PackegeDiscountPreset',
            'CONDITIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' =>
                        [
                            'All' => 'AND',
                            'True' => 'True',
                        ],
                    'CHILDREN' =>
                        [
                        ],
                ],
            'ACTIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' =>
                        [
                            'All' => 'AND',
                        ],
                    'CHILDREN' =>
                        [
                            0 =>
                                [
                                    'CLASS_ID' => 'ActSaleBsktGrp',
                                    'DATA' =>
                                        [
                                            'Type' => 'Discount',
                                            'Value' => 3.0,
                                            'Unit' => 'Perc',
                                            'Max' => 0,
                                            'All' => 'AND',
                                            'True' => 'True',
                                        ],
                                    'CHILDREN' =>
                                        [
                                            0 =>
                                                [
                                                    'CLASS_ID' => 'BasketQuantity:3:42',
                                                    'DATA' =>
                                                        [
                                                            'logic' => 'EqGr',
                                                            'value' => 'QUANTITY',
                                                        ],
                                                ],
                                            1 =>
                                                [
                                                    'CLASS_ID' => 'CondIBProp:3:42',
                                                    'DATA' =>
                                                        [
                                                            'logic' => 'Great',
                                                            'value' => 0.0,
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
            'USER_GROUPS' =>
                [
                    0 => 2,
                ],
        ]);

    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        $helper = new HelperManager();

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $res = \CSaleDiscount::GetList(['ID' => 'ASC'], ['XML_ID' => 'PackegeDiscountPreset'], false, false, ['ID']);
        if($elem = $res->Fetch()) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            \CSaleDiscount::Delete($elem['ID']);
        }
    }

}
