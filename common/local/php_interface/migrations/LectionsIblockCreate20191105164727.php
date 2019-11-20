<?php

namespace Sprint\Migration;


class LectionsIblockCreate20191105164727 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Создание инфоблоков для лендинга Флагманского магазина (лекции)';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $iblock = [
            'IBLOCK_TYPE_ID'     => 'grandin',
            'LID'                => 's1',
            'CODE'               => 'flagman_lections',
            'NAME'               => 'Флагманский магазин: лекции',
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
        
        $iblockId = $helper->Iblock()->addIblockIfNotExists($iblock);
    
        $props[] = [
            'NAME' => 'Свободные места',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => 'FREE_SITS',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '1',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ];
    
        $props[] = [
            'NAME' => 'Всего мест',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => 'SITS',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '1',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ];
    
        foreach ($props as $prop) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, $prop);
        }
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        $helper->Iblock()->deleteIblock($helper->Iblock()->getIblockId('flagman_lections', 'grandin'));
    }
    
}
