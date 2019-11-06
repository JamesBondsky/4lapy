<?php

namespace Sprint\Migration;


class AddFlagmanServicesIblock05112019162747 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Создание инфоблоков для лендинга Флагманского магазина (список сервисов)';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $iblock = [
            'IBLOCK_TYPE_ID'     => 'grandin',
            'LID'                => 's1',
            'CODE'               => 'flagman_services',
            'NAME'               => 'Флагманский магазин: сервисы',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'LIST_PAGE_URL'      => '',
            'DETAIL_PAGE_URL'    => '',
            'SECTION_PAGE_URL'   => '',
            'CANONICAL_PAGE_URL' => '',
            'PICTURE'            => null,
            'DESCRIPTION'        => '',
            'DESCRIPTION_TYPE'   => 'text',
            'RSS_TTL'            => '24',
            'RSS_ACTIVE'         => 'Y',
            'RSS_FILE_ACTIVE'    => 'N',
            'RSS_FILE_LIMIT'     => null,
            'RSS_FILE_DAYS'      => null,
            'RSS_YANDEX_ACTIVE'  => 'N',
            'TMP_ID'             => null,
            'INDEX_ELEMENT'      => 'Y',
            'INDEX_SECTION'      => 'Y',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => null,
            'PROPERTY_INDEX'     => 'I',
            'VERSION'            => '1',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => null,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Разделы',
            'SECTION_NAME'       => 'Раздел',
            'ELEMENTS_NAME'      => 'Элементы',
            'ELEMENT_NAME'       => 'Элемент',
            'EXTERNAL_ID'        => '25',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => '4lapy.ru',
        ];
        
        $helper->Iblock()->addIblockIfNotExists($iblock);
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        $helper->Iblock()->deleteIblock($helper->Iblock()->getIblockId('flagman_services', 'grandin'));
    }
    
}
