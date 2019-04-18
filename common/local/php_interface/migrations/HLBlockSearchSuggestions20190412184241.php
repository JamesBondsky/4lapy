<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class HLBlockSearchSuggestions20190412184241 extends SprintMigrationBase
{

    protected $description = 'Создание HL-блока "Поисковые подсказки"';

    protected static $hlBlockName = 'SearchSuggestions';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(array(
            'NAME' => static::$hlBlockName,
            'TABLE_NAME' => 'b_search_suggestions',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Поисковые подсказки',
                        ),
                ),
        ));
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_SUGGESTION', array(
            'FIELD_NAME' => 'UF_SUGGESTION',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'E',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Подсказка',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Подсказка',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Подсказка',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_RATE', array(
            'FIELD_NAME' => 'UF_RATE',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Рейтинг',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Рейтинг',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Рейтинг',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
    }

    public function down()
    {
        $helper = new HelperManager();

        $helper->Hlblock()->deleteHlblockIfExists(static::$hlBlockName);

    }

}
