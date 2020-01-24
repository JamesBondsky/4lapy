<?php

namespace Sprint\Migration;


class AddAddressUserFieldForLections20200124162359 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Поле адрес для инфоблока лекций";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_41_SECTION', 'UF_LECTION_ADDRESS', [
            'ENTITY_ID'         => 'IBLOCK_41_SECTION',
            'FIELD_NAME'        => 'UF_LECTION_ADDRESS',
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
                    'ru' => 'Адрес',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Адрес',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Адрес',
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
    
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_41_SECTION', 'UF_LECTION_ADDRESS');
    }
    
}
