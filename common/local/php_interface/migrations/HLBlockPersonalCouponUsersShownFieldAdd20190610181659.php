<?php

namespace Sprint\Migration;


use Bitrix\Main\DB\Exception;

class HLBlockPersonalCouponUsersShownFieldAdd20190610181659 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление поля "Купон просмотрен" в HL-блок PersonalCouponUsers';

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

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_SHOWN', [
            'FIELD_NAME'        => 'UF_SHOWN',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE'  => 0,
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Купон просмотрен',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Купон просмотрен',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Купон просмотрен',
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
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_SHOWN');
        } else {
            throw new Exception('Пустой $entityId');
        }
    }

}
