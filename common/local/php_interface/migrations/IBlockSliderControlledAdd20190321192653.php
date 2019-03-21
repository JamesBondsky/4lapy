<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;

class IBlockSliderControlledAdd20190321192653 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "";

    public function up()
    {
        $helper = new HelperManager();

        $helper->Iblock()->addIblockTypeIfNotExists(array(
            'ID' => 'publications',
            'SECTIONS' => 'Y',
            'EDIT_FILE_BEFORE' => '',
            'EDIT_FILE_AFTER' => '',
            'IN_RSS' => 'N',
            'SORT' => '200',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Публикации',
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => 'Публикация',
                        ),
                ),
        ));

        $iblockId = $helper->Iblock()->addIblockIfNotExists(array(
            'IBLOCK_TYPE_ID' => 'publications',
            'LID' => 's1',
            'CODE' => IblockCode::SLIDER_CONTROLLED,
            'NAME' => 'Слайдер на главной с параметрами',
            'ACTIVE' => 'Y',
            'SORT' => '700',
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '',
            'SECTION_PAGE_URL' => '',
            'CANONICAL_PAGE_URL' => '',
            'PICTURE' => NULL,
            'DESCRIPTION' => '',
            'DESCRIPTION_TYPE' => 'text',
            'RSS_TTL' => '24',
            'RSS_ACTIVE' => 'Y',
            'RSS_FILE_ACTIVE' => 'N',
            'RSS_FILE_LIMIT' => NULL,
            'RSS_FILE_DAYS' => NULL,
            'RSS_YANDEX_ACTIVE' => 'N',
            'XML_ID' => '',
            'TMP_ID' => NULL,
            'INDEX_ELEMENT' => 'N',
            'INDEX_SECTION' => 'N',
            'WORKFLOW' => 'N',
            'BIZPROC' => 'N',
            'SECTION_CHOOSER' => 'L',
            'LIST_MODE' => '',
            'RIGHTS_MODE' => 'S',
            'SECTION_PROPERTY' => NULL,
            'PROPERTY_INDEX' => NULL,
            'VERSION' => '1',
            'LAST_CONV_ELEMENT' => '0',
            'SOCNET_GROUP_ID' => NULL,
            'EDIT_FILE_BEFORE' => '',
            'EDIT_FILE_AFTER' => '',
            'SECTIONS_NAME' => 'Разделы',
            'SECTION_NAME' => 'Раздел',
            'ELEMENTS_NAME' => 'Элементы',
            'ELEMENT_NAME' => 'Элемент',
            'EXTERNAL_ID' => '',
            'LANG_DIR' => '/',
            'SERVER_NAME' => '4lapy.ru',
        ));

        $helper->Iblock()->updateIblockFields($iblockId, array(
            'IBLOCK_SECTION' =>
                array(
                    'NAME' => 'Привязка к разделам',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' =>
                        array(
                            'KEEP_IBLOCK_SECTION_ID' => 'N',
                        ),
                ),
            'ACTIVE' =>
                array(
                    'NAME' => 'Активность',
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ),
            'ACTIVE_FROM' =>
                array(
                    'NAME' => 'Начало активности',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '',
                ),
            'ACTIVE_TO' =>
                array(
                    'NAME' => 'Окончание активности',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '',
                ),
            'SORT' =>
                array(
                    'NAME' => 'Сортировка',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '0',
                ),
            'NAME' =>
                array(
                    'NAME' => 'Название',
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => '',
                ),
            'PREVIEW_PICTURE' =>
                array(
                    'NAME' => 'Картинка для анонса',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' =>
                        array(
                            'FROM_DETAIL' => 'N',
                            'SCALE' => 'N',
                            'WIDTH' => '',
                            'HEIGHT' => '',
                            'IGNORE_ERRORS' => 'N',
                            'METHOD' => 'resample',
                            'COMPRESSION' => 100,
                            'DELETE_WITH_DETAIL' => 'N',
                            'UPDATE_WITH_DETAIL' => 'N',
                            'USE_WATERMARK_TEXT' => 'N',
                            'WATERMARK_TEXT' => '',
                            'WATERMARK_TEXT_FONT' => '',
                            'WATERMARK_TEXT_COLOR' => '',
                            'WATERMARK_TEXT_SIZE' => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE' => 'N',
                            'WATERMARK_FILE' => '',
                            'WATERMARK_FILE_ALPHA' => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER' => NULL,
                        ),
                ),
            'PREVIEW_TEXT_TYPE' =>
                array(
                    'NAME' => 'Тип описания для анонса',
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => 'html',
                ),
            'PREVIEW_TEXT' =>
                array(
                    'NAME' => 'Описание для анонса',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '',
                ),
            'DETAIL_PICTURE' =>
                array(
                    'NAME' => 'Детальная картинка',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' =>
                        array(
                            'SCALE' => 'N',
                            'WIDTH' => '',
                            'HEIGHT' => '',
                            'IGNORE_ERRORS' => 'N',
                            'METHOD' => 'resample',
                            'COMPRESSION' => 100,
                            'USE_WATERMARK_TEXT' => 'N',
                            'WATERMARK_TEXT' => '',
                            'WATERMARK_TEXT_FONT' => '',
                            'WATERMARK_TEXT_COLOR' => '',
                            'WATERMARK_TEXT_SIZE' => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE' => 'N',
                            'WATERMARK_FILE' => '',
                            'WATERMARK_FILE_ALPHA' => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER' => NULL,
                        ),
                ),
            'DETAIL_TEXT_TYPE' =>
                array(
                    'NAME' => 'Тип детального описания',
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ),
            'DETAIL_TEXT' =>
                array(
                    'NAME' => 'Детальное описание',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '',
                ),
            'XML_ID' =>
                array(
                    'NAME' => 'Внешний код',
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => '',
                ),
            'CODE' =>
                array(
                    'NAME' => 'Символьный код',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' =>
                        array(
                            'UNIQUE' => 'N',
                            'TRANSLITERATION' => 'N',
                            'TRANS_LEN' => 100,
                            'TRANS_CASE' => 'L',
                            'TRANS_SPACE' => '-',
                            'TRANS_OTHER' => '-',
                            'TRANS_EAT' => 'Y',
                            'USE_GOOGLE' => 'N',
                        ),
                ),
            'TAGS' =>
                array(
                    'NAME' => 'Теги',
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => '',
                ),
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Цветовая схема',
            'ACTIVE' => 'Y',
            'SORT' => '100',
            'CODE' => 'COLOR',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'L',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'Y',
            'IS_REQUIRED' => 'Y',
            'VERSION' => '1',
            'USER_TYPE' => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
            'VALUES' =>
                array(
                    0 =>
                        array(
                            'ID' => '2',
                            'PROPERTY_ID' => '161',
                            'VALUE' => 'Светлый',
                            'DEF' => 'Y',
                            'SORT' => '100',
                            'XML_ID' => 'light',
                            'TMP_ID' => NULL,
                            'EXTERNAL_ID' => 'light',
                            'PROPERTY_NAME' => 'Цветовая схема',
                            'PROPERTY_CODE' => 'COLOR',
                            'PROPERTY_SORT' => '100',
                        ),
                    1 =>
                        array(
                            'ID' => '1',
                            'PROPERTY_ID' => '161',
                            'VALUE' => 'Темный',
                            'DEF' => 'N',
                            'SORT' => '200',
                            'XML_ID' => 'dark',
                            'TMP_ID' => NULL,
                            'EXTERNAL_ID' => 'dark',
                            'PROPERTY_NAME' => 'Цветовая схема',
                            'PROPERTY_CODE' => 'COLOR',
                            'PROPERTY_SORT' => '100',
                        ),
                ),
        ));
        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Крупный текст',
            'ACTIVE' => 'Y',
            'SORT' => '200',
            'CODE' => 'BIG_TEXT',
            'DEFAULT_VALUE' => false,
            'PROPERTY_TYPE' => 'N',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'Y',
            'IS_REQUIRED' => 'N',
            'VERSION' => '1',
            'USER_TYPE' => 'WebArch\\BitrixIblockPropertyType\\YesNoType',
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));
        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Ссылка',
            'ACTIVE' => 'Y',
            'SORT' => '300',
            'CODE' => 'LINK',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '1',
            'USER_TYPE' => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));
        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Регион',
            'ACTIVE' => 'Y',
            'SORT' => '400',
            'CODE' => 'LOCATION',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'Y',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '1',
            'USER_TYPE' => 'sale_location',
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));

        $helper->AdminIblock()->buildElementForm($iblockId, array(
            'Элемент' =>
                array(
                    'ID' => 'ID',
                    'DATE_CREATE' => 'Создан',
                    'TIMESTAMP_X' => 'Изменен',
                    'ACTIVE' => 'Активность',
                    'ACTIVE_FROM' => 'Начало активности',
                    'ACTIVE_TO' => 'Окончание активности',
                    'NAME' => 'Название',
                    'CODE' => 'Символьный код',
                    'XML_ID' => 'Внешний код',
                    'SORT' => 'Сортировка',
                    'IBLOCK_ELEMENT_PROP_VALUE' => 'Значения свойств',
                    'PROPERTY_COLOR' => 'Цветовая схема',
                    'PROPERTY_BIG_TEXT' => 'Крупный текст',
                    'PROPERTY_LINK' => 'Ссылка',
                    'PROPERTY_LOCATION' => 'Регион',
                    'PREVIEW_PICTURE' => 'Картинка для анонса',
                    'PREVIEW_TEXT' => 'Описание для анонса',
                ),
        ));

    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->getIblockId(IblockCode::SLIDER_CONTROLLED);
        $helper->Iblock()->deleteIblockIfExists($iblockId);
    }

}
