<?php

namespace Sprint\Migration;


class HblockOrderInterviews20181004105939 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "";

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => 'OrderInterviews',
            'TABLE_NAME' => 'b_order_interview',
            'LANG' =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Отзывы по заказам',
                        ],
                ],
        ]);
        $entityId  = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ORDER_ID', [
            'FIELD_NAME' => 'UF_ORDER_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'ID заказа',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_INTERVIEWED', [
            'FIELD_NAME' => 'UF_INTERVIEWED',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'DEFAULT_VALUE' => '1',
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Отзыв оставлен',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();
        $helper->Hlblock()->deleteHlblockIfExists('OrderInterviews');

    }

}
