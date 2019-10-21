<?php

namespace Sprint\Migration;


use Bitrix\Main\DB\Exception;

class HLBlockPersonalCouponUsersShownFieldsAdd20190620134811 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление полей "Дата окончания действия" и "Размер скидки в процентах" в HL-блок PersonalCouponUsers';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'PersonalCouponUsers',
            'TABLE_NAME' => 'b_hlbd_personal_coupon_users',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Купоны персональных предложений (привязка к пользователям)',
                        ],
                ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_ACTIVE_TO', [
            'FIELD_NAME'        => 'UF_DATE_ACTIVE_TO',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => '',
            'SORT'              => '800',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' =>
                        [
                            'TYPE'  => 'NONE',
                            'VALUE' => '',
                        ],
                    'USE_SECOND'    => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Дата окончания действия',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Дата окончания действия',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Дата окончания действия',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DISCOUNT_VALUE', [
            'FIELD_NAME'        => 'UF_DISCOUNT_VALUE',
            'USER_TYPE_ID'      => 'double',
            'XML_ID'            => '',
            'SORT'              => '900',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'PRECISION'     => 4,
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0.0,
                    'MAX_VALUE'     => 0.0,
                    'DEFAULT_VALUE' => 0.0,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Размер скидки в процентах',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Размер скидки в процентах',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Размер скидки в процентах',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('PersonalCouponUsers');
        $entityId = 'HLBLOCK_' . $hlblockId;
        if ($entityId) {
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_DATE_ACTIVE_TO');
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_DISCOUNT_VALUE');
        } else {
            throw new Exception('Пустой $entityId');
        }
    }

}
