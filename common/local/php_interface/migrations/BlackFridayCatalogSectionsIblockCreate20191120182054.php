<?php

namespace Sprint\Migration;


class BlackFridayCatalogSectionsIblockCreate20191120182054 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Инфоблок Черной Пятницы для ссылок на разделы каталога";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $helper->Iblock()->addIblockTypeIfNotExists([
            'ID'               => 'grandin',
            'SECTIONS'         => 'Y',
            'EDIT_FILE_BEFORE' => null,
            'EDIT_FILE_AFTER'  => null,
            'IN_RSS'           => 'N',
            'SORT'             => '1000',
            'LANG'             =>
                [
                    'ru' =>
                        [
                            'NAME'         => 'Лендинги',
                            'SECTION_NAME' => 'Разделы',
                            'ELEMENT_NAME' => 'Элементы',
                        ],
                ],
        ]);
        
        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID'     => 'grandin',
            'LID'                => 's1',
            'CODE'               => 'black_friday_sections',
            'NAME'               => 'Черная Пятница: Разделы каталога',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'LIST_PAGE_URL'      => '#SITE_DIR#/grandin/index.php?ID=#IBLOCK_ID#',
            'DETAIL_PAGE_URL'    => '#SITE_DIR#/grandin/detail.php?ID=#ELEMENT_ID#',
            'SECTION_PAGE_URL'   => '#SITE_DIR#/grandin/list.php?SECTION_ID=#SECTION_ID#',
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
            'XML_ID'             => '',
            'TMP_ID'             => null,
            'INDEX_ELEMENT'      => 'Y',
            'INDEX_SECTION'      => 'Y',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => null,
            'PROPERTY_INDEX'     => null,
            'VERSION'            => '1',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => null,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Разделы',
            'SECTION_NAME'       => 'Раздел',
            'ELEMENTS_NAME'      => 'Элементы',
            'ELEMENT_NAME'       => 'Элемент',
            'EXTERNAL_ID'        => '',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => '4lapy.ru',
        ]);
        
        $helper->Iblock()->updateIblockFields($iblockId, [
            'IBLOCK_SECTION'    =>
                [
                    'NAME'          => 'Привязка к разделам',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'KEEP_IBLOCK_SECTION_ID' => 'N',
                        ],
                ],
            'ACTIVE'            =>
                [
                    'NAME'          => 'Активность',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
            'ACTIVE_FROM'       =>
                [
                    'NAME'          => 'Начало активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'ACTIVE_TO'         =>
                [
                    'NAME'          => 'Окончание активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'SORT'              =>
                [
                    'NAME'          => 'Сортировка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '0',
                ],
            'NAME'              =>
                [
                    'NAME'          => 'Название',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
            'PREVIEW_PICTURE'   =>
                [
                    'NAME'          => 'Картинка для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'FROM_DETAIL'             => 'N',
                            'SCALE'                   => 'N',
                            'WIDTH'                   => '',
                            'HEIGHT'                  => '',
                            'IGNORE_ERRORS'           => 'N',
                            'METHOD'                  => 'resample',
                            'COMPRESSION'             => 100,
                            'DELETE_WITH_DETAIL'      => 'N',
                            'UPDATE_WITH_DETAIL'      => 'N',
                            'USE_WATERMARK_TEXT'      => 'N',
                            'WATERMARK_TEXT'          => '',
                            'WATERMARK_TEXT_FONT'     => '',
                            'WATERMARK_TEXT_COLOR'    => '',
                            'WATERMARK_TEXT_SIZE'     => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE'      => 'N',
                            'WATERMARK_FILE'          => '',
                            'WATERMARK_FILE_ALPHA'    => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER'    => null,
                        ],
                ],
            'PREVIEW_TEXT_TYPE' =>
                [
                    'NAME'          => 'Тип описания для анонса',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
            'PREVIEW_TEXT'      =>
                [
                    'NAME'          => 'Описание для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'DETAIL_PICTURE'    =>
                [
                    'NAME'          => 'Детальная картинка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'SCALE'                   => 'N',
                            'WIDTH'                   => '',
                            'HEIGHT'                  => '',
                            'IGNORE_ERRORS'           => 'N',
                            'METHOD'                  => 'resample',
                            'COMPRESSION'             => 100,
                            'USE_WATERMARK_TEXT'      => 'N',
                            'WATERMARK_TEXT'          => '',
                            'WATERMARK_TEXT_FONT'     => '',
                            'WATERMARK_TEXT_COLOR'    => '',
                            'WATERMARK_TEXT_SIZE'     => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE'      => 'N',
                            'WATERMARK_FILE'          => '',
                            'WATERMARK_FILE_ALPHA'    => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER'    => null,
                        ],
                ],
            'DETAIL_TEXT_TYPE'  =>
                [
                    'NAME'          => 'Тип детального описания',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
            'DETAIL_TEXT'       =>
                [
                    'NAME'          => 'Детальное описание',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'XML_ID'            =>
                [
                    'NAME'          => 'Внешний код',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
            'CODE'              =>
                [
                    'NAME'          => 'Символьный код',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'UNIQUE'          => 'N',
                            'TRANSLITERATION' => 'N',
                            'TRANS_LEN'       => 100,
                            'TRANS_CASE'      => 'L',
                            'TRANS_SPACE'     => '-',
                            'TRANS_OTHER'     => '-',
                            'TRANS_EAT'       => 'Y',
                            'USE_GOOGLE'      => 'N',
                        ],
                ],
            'TAGS'              =>
                [
                    'NAME'          => 'Теги',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
        ]);
        
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Ссылка',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'LINK',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'S',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '1',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        
        
    }
    
    public function down()
    {
        $helper = new HelperManager();
    
        $helper->Iblock()->deleteIblock($helper->Iblock()->getIblockId('black_friday_sections', 'grandin'));
        
    }
    
}
