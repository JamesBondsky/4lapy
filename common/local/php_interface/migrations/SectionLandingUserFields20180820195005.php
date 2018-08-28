<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class SectionLandingUserFields20180820195005
 *
 * @package Sprint\Migration
 */
class SectionLandingUserFields20180820195005 extends SprintMigrationBase
{

    protected $description = 'Создание пользовательских полей разделов каталога для лендинга';

    /**
     *
     *
     * @return bool|void
     */
    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_2_SECTION', 'UF_SUB_DOMAIN', [
            'ENTITY_ID' => 'IBLOCK_2_SECTION',
            'FIELD_NAME' => 'UF_SUB_DOMAIN',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '600',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Поддомен для лендинга',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_2_SECTION', 'UF_DEF_FOR_LANDING', [
            'ENTITY_ID' => 'IBLOCK_2_SECTION',
            'FIELD_NAME' => 'UF_DEF_FOR_LANDING',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '600',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'DEFAULT_VALUE' => 0,
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
                    'ru' => 'Раздел является основным для поддомена',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            'IBLOCK_2_SECTION',
            'UF_FORM_TEMPLATE',
            [
                'ENTITY_ID' => 'IBLOCK_2_SECTION',
                'FIELD_NAME' => 'UF_FORM_TEMPLATE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => '600',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    [
                        'SIZE' => 20,
                        'ROWS' => 1,
                        'REGEXP' => '',
                        'MIN_LENGTH' => 0,
                        'MAX_LENGTH' => 0,
                        'DEFAULT_VALUE' => '',
                    ],
                'EDIT_FORM_LABEL' =>
                    [
                        'ru' => 'Шаблон формы расчета размера (например)',
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
            ]
        );
    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        // не треба

    }

}
