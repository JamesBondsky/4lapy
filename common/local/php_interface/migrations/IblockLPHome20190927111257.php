<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockLPHome20190927111257 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет инфоблоки для лендинга Уютно жить";

    public function up(){
        $this->addIblockFaq();
        $this->addIblockDrawnings();
        $this->addSectionSlider();
        $this->addSectionArticles();
    }

    public function addIblockFaq()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists(array(
            "IBLOCK_TYPE_ID" => "grandin",
            "LID" => "s1",
            "CODE" => "home_faq",
            "NAME" => "Уютно жить: вопросы и ответы",
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
            "SECTION_PROPERTY" => "N",
            "PROPERTY_INDEX" => "N",
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
    }

    public function addIblockDrawnings()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists(array(
            "IBLOCK_TYPE_ID" => "grandin",
            "LID" => "s1",
            "CODE" => "home_images",
            "NAME" => "Уютно жить: заявки",
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
            "EXTERNAL_ID" => "",
            "LANG_DIR" => "/",
            "SERVER_NAME" => "4lapy.ru",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "id пользователя",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "USER_ID",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
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
            "USER_TYPE" => "UserID",
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "логин",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "LOGIN",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => "",
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "",
            "USER_TYPE_SETTINGS" => "",
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "ФИО",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "FIO",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => "",
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "",
            "USER_TYPE_SETTINGS" => "",
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "телефон",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "PHONE",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => "",
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "",
            "USER_TYPE_SETTINGS" => "",
            "HINT" => "",
        ));

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            "NAME" => "почта",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "EMAIL",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "N",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => "",
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "N",
            "VERSION" => "1",
            "USER_TYPE" => "",
            "USER_TYPE_SETTINGS" => "",
            "HINT" => "",
        ));
    }

    public function addSectionSlider()
    {
        $section = [
            "IBLOCK_SECTION_ID" => "",
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y",
            "SORT" => "500",
            "NAME" => "Уютно жить",
            "PICTURE" => "",
            "DEPTH_LEVEL" => "1",
            "DESCRIPTION" => "",
            "DESCRIPTION_TYPE" => "text",
            "SEARCHABLE_CONTENT" => "УЮТНО ЖИТЬ",
            "CODE" => "home",
            "XML_ID" => "",
            "TMP_ID" => "",
            "DETAIL_PICTURE" => "",
            "SOCNET_GROUP_ID" => "",
            "LIST_PAGE_URL" => "",
            "SECTION_PAGE_URL" => "",
            "IBLOCK_TYPE_ID" => "grandin",
            "IBLOCK_CODE" => "fashion_footer_product",
            "EXTERNAL_ID" => "",
        ];

        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::CATALOG_SLIDER_PRODUCTS);
        $helper->Iblock()->addSectionIfNotExists($iblockId, $section);
    }

    public function addSectionArticles()
    {
        $section = [
            "IBLOCK_SECTION_ID" => "",
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y",
            "SORT" => "500",
            "NAME" => "LP Уютно жить",
            "PICTURE" => "",
            "DEPTH_LEVEL" => "1",
            "DESCRIPTION" => "",
            "DESCRIPTION_TYPE" => "text",
            "SEARCHABLE_CONTENT" => "LP УЮТНО ЖИТЬ ",
            "CODE" => "home",
            "XML_ID" => "",
            "TMP_ID" => "",
            "DETAIL_PICTURE" => "",
            "SOCNET_GROUP_ID" => "",
        ];

        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::CATALOG_SLIDER_PRODUCTS);
        $helper->Iblock()->addSectionIfNotExists($iblockId, $section);
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
