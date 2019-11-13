<?php

namespace Sprint\Migration;


class LectionsTableFieldAdd20191113151635 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Добавление поля к хл блоку";
    
    public function up()
    {
        $helper = new HelperManager();
        
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_135', 'UF_EMAIL', [
            'ENTITY_ID'         => 'HLBLOCK_135',
            'FIELD_NAME'        => 'UF_EMAIL',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_EMAIL',
            'SORT'              => '20',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => null,
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => null,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Email',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Email',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Email',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => null,
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => null,
                ],
        ]);
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        //your code ...
        
    }
    
}
