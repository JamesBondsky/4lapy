<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;

class IblockFashionAdd20190822125321 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет инфоблоки для лендинга \"Одежда\"";

    public function up(){
        $this->addSliderProduct();
        $this->addCategoryProduct();
    }

    public function addSliderProduct()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists(array(
            "IBLOCK_TYPE_ID" => "grandin",
            "LID" => "s1",
            "CODE" => "fashion_slider_product",
            "NAME" => "Одежда: слайдер с товарами",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "LIST_PAGE_URL" => "",
            "DETAIL_PAGE_URL" => "",
            "SECTION_PAGE_URL" => "",
            "CANONICAL_PAGE_URL" => "",
            "PICTURE" => null,
            "DESCRIPTION" => "",
            "DESCRIPTION_TYPE" => "text",
            "RSS_TTL" => "24",
            "RSS_ACTIVE" => "Y",
            "RSS_FILE_ACTIVE" => "N",
            "RSS_FILE_LIMIT" => null,
            "RSS_FILE_DAYS" => null,
            "RSS_YANDEX_ACTIVE" => "N",
            "XML_ID" => "27",
            "TMP_ID" => null,
            "INDEX_ELEMENT" => "Y",
            "INDEX_SECTION" => "Y",
            "WORKFLOW" => "N",
            "BIZPROC" => "N",
            "SECTION_CHOOSER" => "L",
            "LIST_MODE" => "",
            "RIGHTS_MODE" => "S",
            "SECTION_PROPERTY" => null,
            "PROPERTY_INDEX" => null,
            "VERSION" => "1",
            "LAST_CONV_ELEMENT" => "0",
            "SOCNET_GROUP_ID" => null,
            "EDIT_FILE_BEFORE" => "",
            "EDIT_FILE_AFTER" => "",
            "SECTIONS_NAME" => "Разделы",
            "SECTION_NAME" => "Раздел",
            "ELEMENTS_NAME" => "Элементы",
            "ELEMENT_NAME" => "Элемент",
            "EXTERNAL_ID" => "27",
            "LANG_DIR" => "/",
            "SERVER_NAME" => "4lapy.ru",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Картинки",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "IMAGES",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "F",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "XML_ID" => "190",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Товары",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "PRODUCTS",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "XML_ID" => "191",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "ElementXmlID",
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));
    }

    public function addCategoryProduct()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists(array(
            "IBLOCK_TYPE_ID" => "grandin",
            "LID" => "s1",
            "CODE" => "fashion_footer_product",
            "NAME" => "Одежда: категории товаров",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "LIST_PAGE_URL" => "",
            "DETAIL_PAGE_URL" => "",
            "SECTION_PAGE_URL" => "",
            "CANONICAL_PAGE_URL" => "",
            "PICTURE" => null,
            "DESCRIPTION" => "",
            "DESCRIPTION_TYPE" => "text",
            "RSS_TTL" => "24",
            "RSS_ACTIVE" => "Y",
            "RSS_FILE_ACTIVE" => "N",
            "RSS_FILE_LIMIT" => null,
            "RSS_FILE_DAYS" => null,
            "RSS_YANDEX_ACTIVE" => "N",
            "XML_ID" => "",
            "TMP_ID" => null,
            "INDEX_ELEMENT" => "Y",
            "INDEX_SECTION" => "Y",
            "WORKFLOW" => "N",
            "BIZPROC" => "N",
            "SECTION_CHOOSER" => "L",
            "LIST_MODE" => "",
            "RIGHTS_MODE" => "S",
            "SECTION_PROPERTY" => null,
            "PROPERTY_INDEX" => "I",
            "VERSION" => "1",
            "LAST_CONV_ELEMENT" => "0",
            "SOCNET_GROUP_ID" => null,
            "EDIT_FILE_BEFORE" => "",
            "EDIT_FILE_AFTER" => "",
            "SECTIONS_NAME" => "Разделы",
            "SECTION_NAME" => "Раздел",
            "ELEMENTS_NAME" => "Элементы",
            "ELEMENT_NAME" => "Элемент",
            "EXTERNAL_ID" => "",
            "LANG_DIR" => "/",
            "SERVER_NAME" => "4lapy.ru",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Миниатюра",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "TITLE_IMAGE",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "F",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Товары",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "PRODUCTS",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "ElementXmlID",
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Картинка",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "IMAGE",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "F",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "Привязка к разделу",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "SECTION",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "G",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "2",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "Y",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));
    }

    public function down(){
        $helper = new HelperManager();
        $helper->Iblock()->deleteIblockIfExists('fashion_slider_product');
        $helper->Iblock()->deleteIblockIfExists('fashion_footer_product');
        return true;
    }

}
