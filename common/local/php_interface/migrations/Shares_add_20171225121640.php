<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Shares_add_20171225121640 extends SprintMigrationBase
{
    const IBLOCK_CODE = 'shares';
    
    protected $description = 'Добавление информационного блока акций';
    
    /**
     * Add shares iblock
     */
    public function up()
    {
        $helper = new HelperManager();
        
        $helper->Iblock()->addIblockTypeIfNotExists([
                                                        'ID'               => 'publications',
                                                        'SECTIONS'         => 'Y',
                                                        'EDIT_FILE_BEFORE' => '',
                                                        'EDIT_FILE_AFTER'  => '',
                                                        'IN_RSS'           => 'N',
                                                        'SORT'             => '200',
                                                        'LANG'             => [
                                                            'ru' => [
                                                                'NAME'         => 'Публикации',
                                                                'SECTION_NAME' => '',
                                                                'ELEMENT_NAME' => 'Публикация',
                                                            ],
                                                        ],
                                                    ]);
        
        $iblockId = $helper->Iblock()->addIblockIfNotExists([
                                                                'IBLOCK_TYPE_ID'     => 'publications',
                                                                'LID'                => 's1',
                                                                'CODE'               => self::IBLOCK_CODE,
                                                                'NAME'               => 'Акции',
                                                                'ACTIVE'             => 'Y',
                                                                'SORT'               => '30',
                                                                'LIST_PAGE_URL'      => '#SITE_DIR#/services/shares/',
                                                                'DETAIL_PAGE_URL'    => '#SITE_DIR#/services/shares/#ELEMENT_CODE#/',
                                                                'SECTION_PAGE_URL'   => '#SITE_DIR#/services/shares/',
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
                                                                'INDEX_ELEMENT'      => 'N',
                                                                'INDEX_SECTION'      => 'N',
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
                                                                'SECTIONS_NAME'      => '',
                                                                'SECTION_NAME'       => '',
                                                                'ELEMENTS_NAME'      => 'Акции',
                                                                'ELEMENT_NAME'       => 'Акция',
                                                                'EXTERNAL_ID'        => '',
                                                                'LANG_DIR'           => '/',
                                                                'SERVER_NAME'        => '4lapy.ru',
                                                            ]);
        
        $helper->Iblock()->updateIblockFields($iblockId,
                                              [
                                                  'IBLOCK_SECTION'    => [
                                                      'NAME'          => 'Привязка к разделам',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => [
                                                          'KEEP_IBLOCK_SECTION_ID' => 'N',
                                                      ],
                                                  ],
                                                  'ACTIVE'            => [
                                                      'NAME'          => 'Активность',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => 'Y',
                                                  ],
                                                  'ACTIVE_FROM'       => [
                                                      'NAME'          => 'Начало активности',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'ACTIVE_TO'         => [
                                                      'NAME'          => 'Окончание активности',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'SORT'              => [
                                                      'NAME'          => 'Сортировка',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '0',
                                                  ],
                                                  'NAME'              => [
                                                      'NAME'          => 'Название',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'PREVIEW_PICTURE'   => [
                                                      'NAME'          => 'Картинка для анонса',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => [
                                                          'FROM_DETAIL'             => 'N',
                                                          'SCALE'                   => 'N',
                                                          'WIDTH'                   => '',
                                                          'HEIGHT'                  => '',
                                                          'IGNORE_ERRORS'           => 'N',
                                                          'METHOD'                  => 'resample',
                                                          'COMPRESSION'             => 95,
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
                                                  'PREVIEW_TEXT_TYPE' => [
                                                      'NAME'          => 'Тип описания для анонса',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => 'text',
                                                  ],
                                                  'PREVIEW_TEXT'      => [
                                                      'NAME'          => 'Описание для анонса',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'DETAIL_PICTURE'    => [
                                                      'NAME'          => 'Детальная картинка',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => [
                                                          'SCALE'                   => 'N',
                                                          'WIDTH'                   => '',
                                                          'HEIGHT'                  => '',
                                                          'IGNORE_ERRORS'           => 'N',
                                                          'METHOD'                  => 'resample',
                                                          'COMPRESSION'             => 95,
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
                                                  'DETAIL_TEXT_TYPE'  => [
                                                      'NAME'          => 'Тип детального описания',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => 'html',
                                                  ],
                                                  'DETAIL_TEXT'       => [
                                                      'NAME'          => 'Детальное описание',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'XML_ID'            => [
                                                      'NAME'          => 'Внешний код',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                                  'CODE'              => [
                                                      'NAME'          => 'Символьный код',
                                                      'IS_REQUIRED'   => 'Y',
                                                      'DEFAULT_VALUE' => [
                                                          'UNIQUE'          => 'Y',
                                                          'TRANSLITERATION' => 'Y',
                                                          'TRANS_LEN'       => 100,
                                                          'TRANS_CASE'      => 'L',
                                                          'TRANS_SPACE'     => '-',
                                                          'TRANS_OTHER'     => '-',
                                                          'TRANS_EAT'       => 'Y',
                                                          'USE_GOOGLE'      => 'N',
                                                      ],
                                                  ],
                                                  'TAGS'              => [
                                                      'NAME'          => 'Теги',
                                                      'IS_REQUIRED'   => 'N',
                                                      'DEFAULT_VALUE' => '',
                                                  ],
                                              ]);
        
        $helper->Iblock()->addPropertyIfNotExists($iblockId,
                                                  [
                                                      'NAME'               => 'Тип акции',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '10',
                                                      'CODE'               => 'SHARE_TYPE',
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
                                                      'USER_TYPE'          => 'directory',
                                                      'USER_TYPE_SETTINGS' => [
                                                          'size'       => 1,
                                                          'width'      => 0,
                                                          'group'      => 'N',
                                                          'multiple'   => 'N',
                                                          'TABLE_NAME' => 'b_hlbd_sharetype',
                                                      ],
                                                      'HINT'               => '',
                                                  ]);
        $helper->Iblock()->addPropertyIfNotExists($iblockId,
                                                  [
                                                      'NAME'               => 'Тип',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '20',
                                                      'CODE'               => 'TYPE',
                                                      'DEFAULT_VALUE'      => '',
                                                      'PROPERTY_TYPE'      => 'S',
                                                      'ROW_COUNT'          => '1',
                                                      'COL_COUNT'          => '30',
                                                      'LIST_TYPE'          => 'L',
                                                      'MULTIPLE'           => 'Y',
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
                                                      'USER_TYPE'          => 'directory',
                                                      'USER_TYPE_SETTINGS' => [
                                                          'size'       => 1,
                                                          'width'      => 0,
                                                          'group'      => 'N',
                                                          'multiple'   => 'N',
                                                          'TABLE_NAME' => 'b_hlbd_forwho',
                                                      ],
                                                      'HINT'               => '',
                                                  ]);
        $helper->Iblock()->addPropertyIfNotExists($iblockId,
                                                  [
                                                      'NAME'               => 'Отображать только для Мобильного приложения',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '30',
                                                      'CODE'               => 'ONLY_MP',
                                                      'DEFAULT_VALUE'      => 0,
                                                      'PROPERTY_TYPE'      => 'N',
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
                                                      'USER_TYPE'          => 'YesNoPropertyType',
                                                      'USER_TYPE_SETTINGS' => null,
                                                      'HINT'               => '',
                                                  ]);
        $helper->Iblock()->addPropertyIfNotExists($iblockId,
                                                  [
                                                      'NAME'               => 'Короткий url',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '80',
                                                      'CODE'               => 'SHORT_URL',
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
        $helper->Iblock()->addPropertyIfNotExists($iblockId,
                                                  [
                                                      'NAME'               => 'Старый url',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '90',
                                                      'CODE'               => 'OLD_URL',
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
    
    /**
     * Remove shares iblock
     */
    public function down()
    {
        $iblockHelper = (new HelperManager())->Iblock();
        $iblockHelper->deleteIblock($iblockHelper->getIblockId(self::IBLOCK_CODE));
    }
    
}
