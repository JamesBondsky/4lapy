<?php

namespace Sprint\Migration;


class AddNewUserField20191212165859 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Добавление свойства для хранения временного токена";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_DISPONSABLE_TOKEN', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_DISPONSABLE_TOKEN',
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
                    'ru' => 'Одноразовый токен',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Одноразовый токен',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Одноразовый токен',
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
    
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('USER', 'UF_DISPONSABLE_TOKEN');
    }
    
}
