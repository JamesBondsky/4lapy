<?php

namespace Sprint\Migration;


class BlackFridaySectionsPropertyLinkCreate20191121132020 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $iblockCode  = 'black_friday_sections';
    protected $description = "Ссылка на раздел";
    
    public function up()
    {
        $id     = \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
        $helper = new HelperManager();
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $id . '_SECTION', 'UF_LINK', [
            'ENTITY_ID'         => 'IBLOCK_' . $id . '_SECTION',
            'FIELD_NAME'        => 'UF_LINK',
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
                    'ru' => 'Сссылка',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Сссылка',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Сссылка',
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
        $id     = \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
        $helper = new HelperManager();
        
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $id . '_SECTION', 'UF_LINK');
    }
}
