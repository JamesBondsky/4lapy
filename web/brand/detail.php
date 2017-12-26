<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<div class="b-container">
	<?$APPLICATION->IncludeComponent(
        'bitrix:news.detail',
        'fp.17.0.brand',
        array(
            'ELEMENT_ID' => '',
            'ELEMENT_CODE' => $_REQUEST['ELEMENT_CODE'],
            'IBLOCK_TYPE' => \FourPaws\Enum\IblockType::CATALOG,
            'IBLOCK_ID' => \FourPaws\Enum\IblockCode::BRANDS,
            'FIELD_CODE' => array(
                'NAME',
                'PREVIEW_PICTURE',
                'DETAIL_TEXT',
                'DETAIL_PICTURE',
            ),
            'PROPERTY_CODE' => array(),
            'CACHE_GROUPS' => 'N',
            'CACHE_TIME' => '43200',
            'CACHE_TYPE' => 'A',
            'DETAIL_URL' => '',
            'RESIZE_WIDTH' => '90',
            'RESIZE_HEIGHT' => '90',
            'RESIZE_TYPE' => 'BX_RESIZE_IMAGE_PROPORTIONAL',
            'ACTIVE_DATE_FORMAT' => 'd.m.Y',
            'ADD_SECTIONS_CHAIN' => 'N',
            'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
            'INCLUDE_SUBSECTIONS' => 'N',
            'PARENT_SECTION' => '',
            'PARENT_SECTION_CODE' => '',
            'PREVIEW_TRUNCATE_LEN' => '',
            'SET_BROWSER_TITLE' => 'Y',
            'SET_LAST_MODIFIED' => 'Y',
            'SET_META_DESCRIPTION' => 'Y',
            'SET_META_KEYWORDS' => 'Y',
            'SET_STATUS_404' => 'Y',
            'SET_TITLE' => 'Y',
            'SHOW_404' => 'Y',
            'FILE_404' => '/404.php',
            'CHECK_DATES' => 'Y',
            'IBLOCK_URL' => '',
            'SET_CANONICAL_URL' => 'N',
            'BROWSER_TITLE' => 'ELEMENT_META_TITLE',
            'META_KEYWORDS' => 'ELEMENT_META_KEYWORDS',
            'META_DESCRIPTION' => 'ELEMENT_META_DESCRIPTION',
            'ADD_ELEMENT_CHAIN' => 'N',
            'USE_PERMISSIONS' => 'N',
            'STRICT_SECTION_CHECK' => 'N',
            'DISPLAY_TOP_PAGER' => 'N',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'MESSAGE_404' => '',

            'PAGER_BASE_LINK_ENABLE' => 'N',
            'PAGER_DESC_NUMBERING' => 'N',
            'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
            'PAGER_SHOW_ALL' => 'N',
            'PAGER_SHOW_ALWAYS' => 'N',
            'PAGER_TEMPLATE' => '',
            'PAGER_TITLE' => '',
            'AJAX_MODE' => 'N',
            'AJAX_OPTION_ADDITIONAL' => '',
            'AJAX_OPTION_HISTORY' => 'N',
            'AJAX_OPTION_JUMP' => 'N',
            'AJAX_OPTION_STYLE' => 'N',
        ),
        null,
        array(
            'HIDE_ICONS' => 'Y'
        )
    );?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>