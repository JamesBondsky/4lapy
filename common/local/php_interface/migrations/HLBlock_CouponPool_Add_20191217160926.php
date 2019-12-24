<?php

namespace Sprint\Migration;


class HLBlock_CouponPool_Add_20191217160926 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Создает HL-блок "Пул купонов"';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'CouponPool',
            'TABLE_NAME' => 'b_hlbd_coupon_pool',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Пул купонов',
                        ],
                ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PROMO_CODE', [
            'FIELD_NAME'        => 'UF_PROMO_CODE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'E',
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
                    'ru' => 'Промокод',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Промокод',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Промокод',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_OFFER', [
            'FIELD_NAME'        => 'UF_OFFER',
            'USER_TYPE_ID'      => 'iblock_element',
            'XML_ID'            => '',
            'SORT'              => '200',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'IBLOCK_ID'     => 20,
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'ID персонального предложения',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'ID персонального предложения',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'ID персонального предложения',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_CREATED', [
            'FIELD_NAME'        => 'UF_DATE_CREATED',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => '',
            'SORT'              => '300',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' =>
                        [
                            'TYPE'  => 'NOW',
                            'VALUE' => '',
                        ],
                    'USE_SECOND'    => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Когда создано',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Когда создано',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Когда создано',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_CHANGED', [
            'FIELD_NAME'        => 'UF_DATE_CHANGED',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => '',
            'SORT'              => '400',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' =>
                        [
                            'TYPE'  => 'NOW',
                            'VALUE' => '',
                        ],
                    'USE_SECOND'    => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Когда изменено',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Когда изменено',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Когда изменено',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TAKEN', [
            'FIELD_NAME'        => 'UF_TAKEN',
            'USER_TYPE_ID'      => 'integer',
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
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0,
                    'MAX_VALUE'     => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Взят из пула',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Взят из пула',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Взят из пула',
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

        $helper->Hlblock()->deleteHlblockIfExists('CouponPool');

    }

}
