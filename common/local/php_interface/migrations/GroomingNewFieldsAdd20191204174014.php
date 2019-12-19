<?php

namespace Sprint\Migration;

use Bitrix\Highloadblock\HighloadBlockTable;

class GroomingNewFieldsAdd20191204174014 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Добавление новых полей для таблицы заявок груминга";
    
    public function up()
    {
        $helper = new HelperManager();
    
        $id = HighloadBlockTable::query()
            ->setSelect(['ID'])
            ->setFilter(['=NAME' => 'GroomingApps'])
            ->exec()
            ->fetch()['ID'];
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_' . $id, 'UF_DATE', [
            'ENTITY_ID'         => 'HLBLOCK_' . $id,
            'FIELD_NAME'        => 'UF_DATE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
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
                    'ru' => 'Дата',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Дата',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Дата',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_' . $id, 'UF_CLINIC', [
            'ENTITY_ID'         => 'HLBLOCK_' . $id,
            'FIELD_NAME'        => 'UF_CLINIC',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
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
                    'ru' => 'Клиника',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Клиника',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Клиника',
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
        
        //your code ...
    }
    
}
