<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class DobrolapMagnetUserField20190725160000 extends SprintMigrationBase
{

    protected $description = 'Добавление флага "Подарить магнит Добролдап"';

    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_GIFT_DOBROLAP', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_GIFT_DOBROLAP',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '999',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
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
                    'ru' => 'Подарить магнит Добролдап',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Подарить магнит Добролдап',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Подарить магнит Добролдап',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Подарить магнит Добролдап',
                ],
        ]);
    }
}
