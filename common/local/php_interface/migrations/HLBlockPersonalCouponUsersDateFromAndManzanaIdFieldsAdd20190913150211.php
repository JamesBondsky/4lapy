<?php

namespace Sprint\Migration;


use Bitrix\Main\Application;
use Bitrix\Main\DB\Exception;

class HLBlockPersonalCouponUsersDateFromAndManzanaIdFieldsAdd20190913150211 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление полей "Дата начала действия" и "Manzana ID" в HL-блок PersonalCouponUsers';

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

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_ACTIVE_FROM', [
            'FIELD_NAME'        => 'UF_DATE_ACTIVE_FROM',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => '',
            'SORT'              => '795',
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
                    'ru' => 'Дата начала действия',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Дата начала действия',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Дата начала действия',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_MANZANA_ID', [
            'FIELD_NAME'        => 'UF_MANZANA_ID',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '1100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Manzana ID',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Manzana ID',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Manzana ID',
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

        Application::getConnection()->query('create index b_hlbd_personal_coupon_users_UF_MANZANA_ID_index
    on b_hlbd_personal_coupon_users (UF_MANZANA_ID(36) desc);');
    }

    public function down()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('PersonalCouponUsers');
        $entityId = 'HLBLOCK_' . $hlblockId;
        if ($entityId) {
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_DATE_ACTIVE_FROM');
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_MANZANA_ID');
        } else {
            throw new Exception('Пустой $entityId');
        }

    }

}
