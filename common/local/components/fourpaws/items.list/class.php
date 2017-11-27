<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @noinspection PhpUndefinedClassInspection */
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

use Bitrix\Iblock;
use Bitrix\Main\{
    Context, Loader, Type\DateTime
};

/** @noinspection AutoloadingIssuesInspection */
class CItemsListComponent extends CBitrixComponent
{
    protected $arrFilter = [];
    
    protected $arNavParams;
    
    protected $pagerParameters;
    
    /**
     * @param $arParams
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function onPrepareComponentParams($arParams) : array
    {
        //Установка времени кеширвоания если не задано
        if (!isset($arParams['CACHE_TIME'])) {
            $arParams['CACHE_TIME'] = 36000000;
        }
        
        if (is_array($arParams['IBLOCK_ID'])) {
            foreach ($arParams['IBLOCK_ID'] as $key => $id) {
                if (!is_numeric($id) || (int)$id <= 0) {
                    unset($arParams['IBLOCK_ID'][$key]);
                }
            }
        } elseif (is_numeric($arParams['IBLOCK_ID']) && (int)$arParams['IBLOCK_ID'] > 0) {
            $arParams['IBLOCK_ID'] = (int)$arParams['IBLOCK_ID'];
        }
        if (empty($arParams['IBLOCK_ID'])) {
            //Получение инфоблоков если не установлены
            $params = [
                'select' => ['ID'],
                'filter' => ['ACTIVE' => 'Y'],
            ];
            if (!empty($arParams['IBLOCK_TYPE'])) {
                $params['filter']['IBLOCK_TYPE_ID'] = $arParams['IBLOCK_TYPE'];
            }
            $res = Iblock\IblockTable::getList($params);
            while ($item = $res->fetch()) {
                $arParams['IBLOCK_ID'][] = (int)$item['ID'];
            }
        }
        $arParams['SET_LAST_MODIFIED'] = $arParams['SET_LAST_MODIFIED'] === 'Y';
        
        $arParams['SORT_BY1'] = trim($arParams['SORT_BY1']);
        if (strlen($arParams['SORT_BY1']) <= 0) {
            $arParams['SORT_BY1'] = 'ACTIVE_FROM';
        }
        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams['SORT_ORDER1'])) {
            $arParams['SORT_ORDER1'] = 'DESC';
        }
        
        if (strlen($arParams['SORT_BY2']) <= 0) {
            $arParams['SORT_BY2'] = 'SORT';
        }
        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams['SORT_ORDER2'])) {
            $arParams['SORT_ORDER2'] = 'ASC';
        }
        
        $arParams['CHECK_DATES'] = $arParams['CHECK_DATES'] !== 'N';
        
        if (!is_array($arParams['FIELD_CODE'])) {
            $arParams['FIELD_CODE'] = [];
        }
        foreach ($arParams['FIELD_CODE'] as $key => $val) {
            if (!$val) {
                unset($arParams['FIELD_CODE'][$key]);
            }
        }
        
        if (!is_array($arParams['PROPERTY_CODE'])) {
            $arParams['PROPERTY_CODE'] = [];
        }
        foreach ($arParams['PROPERTY_CODE'] as $key => $val) {
            if ($val === '') {
                unset($arParams['PROPERTY_CODE'][$key]);
            }
        }
        
        $arParams['NEWS_COUNT'] = (int)$arParams['NEWS_COUNT'];
        if ($arParams['NEWS_COUNT'] <= 0) {
            $arParams['NEWS_COUNT'] = 20;
        }
        
        $arParams['CACHE_FILTER'] = $arParams['CACHE_FILTER'] === 'Y';
        
        $arParams['ACTIVE_DATE_FORMAT'] = trim($arParams['ACTIVE_DATE_FORMAT']);
        if (strlen($arParams['ACTIVE_DATE_FORMAT']) <= 0) {
            $arParams['ACTIVE_DATE_FORMAT'] = \Bitrix\Main\Type\Date::getFormat();
        }
        $arParams['PREVIEW_TRUNCATE_LEN']     = (int)$arParams['PREVIEW_TRUNCATE_LEN'];
        $arParams['HIDE_LINK_WHEN_NO_DETAIL'] = $arParams['HIDE_LINK_WHEN_NO_DETAIL'] === 'Y';
        
        $arParams['DISPLAY_TOP_PAGER']               = $arParams['DISPLAY_TOP_PAGER'] === 'Y';
        $arParams['DISPLAY_BOTTOM_PAGER']            = $arParams['DISPLAY_BOTTOM_PAGER'] !== 'N';
        $arParams['PAGER_TITLE']                     = trim($arParams['PAGER_TITLE']);
        $arParams['PAGER_SHOW_ALWAYS']               = $arParams['PAGER_SHOW_ALWAYS'] === 'Y';
        $arParams['PAGER_TEMPLATE']                  = trim($arParams['PAGER_TEMPLATE']);
        $arParams['PAGER_DESC_NUMBERING']            = $arParams['PAGER_DESC_NUMBERING'] === 'Y';
        $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] = (int)$arParams['PAGER_DESC_NUMBERING_CACHE_TIME'];
        $arParams['PAGER_SHOW_ALL']                  = $arParams['PAGER_SHOW_ALL'] === 'Y';
        $arParams['CHECK_PERMISSIONS']               = $arParams['CHECK_PERMISSIONS'] !== 'N';
        
        return $arParams;
    }
    
    public function executeComponent()
    {
        global $USER, $APPLICATION;
        
        $this->setFrameMode(true);
        
        $this->arResult['IBLOCK_ID']      = $this->arParams['IBLOCK_ID'];
        $this->arResult['IBLOCK_TYPE_ID'] = $this->arParams['IBLOCK_TYPE'];
        
        $this->setFilter();
        list($this->arNavParams, $arNavigation, $this->pagerParameters) = $this->setPageParams();
        
        $bUSER_HAVE_ACCESS = $this->checkPermission($USER);
        
        if ($this->startResultCache(false,
                                    [
                                        $this->arParams['CACHE_GROUPS'] === 'N' ? false : $USER->GetGroups(),
                                        $bUSER_HAVE_ACCESS,
                                        $arNavigation,
                                        $this->arrFilter,
                                        $this->pagerParameters,
                                    ])) {
            $this->checkModule();
            
            $this->arResult = [];
            
            $this->setIblocks();
            
            $this->arResult['USER_HAVE_ACCESS'] = $bUSER_HAVE_ACCESS;
            
            $this->setItems();
            
            $this->includeComponentTemplate();
        }
        
        if (isset($this->arResult['ID'])) {
            $arTitleOptions = null;
            if ($USER->IsAuthorized()) {
                if ($this->arParams['SET_TITLE']
                    || $APPLICATION->GetShowIncludeAreas()) {
                    if (Loader::includeModule('iblock')) {
                        $arButtons = CIBlock::GetPanelButtons($this->arResult['ID'],
                                                              0,
                                                              0,
                                                              ['SECTION_BUTTONS' => false]);
                        
                        /** @noinspection NotOptimalIfConditionsInspection */
                        if ($APPLICATION->GetShowIncludeAreas()) {
                            $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(),
                                                                                 $arButtons));
                        }
                        
                        /** @noinspection NotOptimalIfConditionsInspection */
                        if ($this->arParams['SET_TITLE']) {
                            $arTitleOptions = [
                                'ADMIN_EDIT_LINK'  => $arButtons['submenu']['edit_iblock']['ACTION'],
                                'PUBLIC_EDIT_LINK' => '',
                                'COMPONENT_NAME'   => $this->getName(),
                            ];
                        }
                    }
                }
            }
            
            $this->setTemplateCachedData($this->arResult['NAV_CACHED_DATA']);
            
            if ($this->arParams['SET_LAST_MODIFIED'] && $this->arResult['ITEMS_TIMESTAMP_X']) {
                Context::getCurrent()->getResponse()->setLastModified($this->arResult['ITEMS_TIMESTAMP_X']);
            }
            
            return $this->arResult['ELEMENTS'];
        }
        
        return true;
    }
    
    protected function setFilter()
    {
        if (strlen($this->arParams['FILTER_NAME']) <= 0
            || !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/',
                           $this->arParams['FILTER_NAME'])) {
            $this->arrFilter = [];
        } else {
            $this->arrFilter = $GLOBALS[$this->arParams['FILTER_NAME']];
            if (!is_array($this->arrFilter)) {
                $this->arrFilter = [];
            }
        }
        
        if (!$this->arParams['CACHE_FILTER'] && count($this->arrFilter) > 0) {
            $this->arParams['CACHE_TIME'] = 0;
        }
    }
    
    /**
     * @return array
     */
    protected function setPageParams() : array
    {
        if ($this->arParams['DISPLAY_TOP_PAGER'] || $this->arParams['DISPLAY_BOTTOM_PAGER']) {
            $arNavParams = [
                'nPageSize'          => $this->arParams['NEWS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll'           => $this->arParams['PAGER_SHOW_ALL'],
            ];
            /** @noinspection PhpUndefinedClassInspection */
            $arNavigation = \CDBResult::GetNavParams($arNavParams);
            if ($arNavigation['PAGEN'] === 0 && $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] > 0) {
                $this->arParams['CACHE_TIME'] = $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'];
            }
        } else {
            $arNavParams  = [
                'nTopCount'          => $this->arParams['NEWS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
            ];
            $arNavigation = false;
        }
        
        if (empty($this->arParams['PAGER_PARAMS_NAME'])
            || !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/',
                           $this->arParams['PAGER_PARAMS_NAME'])) {
            $pagerParameters = [];
        } else {
            $pagerParameters = $GLOBALS[$this->arParams['PAGER_PARAMS_NAME']];
            if (!is_array($pagerParameters)) {
                $pagerParameters = [];
            }
        }
        
        return [
            $arNavParams,
            $arNavigation,
            $pagerParameters,
        ];
    }
    
    /**
     * @param $USER
     *
     * @return bool
     */
    protected function checkPermission($USER) : bool
    {
        $this->arParams['USE_PERMISSIONS'] = $this->arParams['USE_PERMISSIONS'] === 'Y';
        if (!is_array($this->arParams['GROUP_PERMISSIONS'])) {
            $this->arParams['GROUP_PERMISSIONS'] = [1];
        }
        
        $bUSER_HAVE_ACCESS = !$this->arParams['USE_PERMISSIONS'];
        if ($this->arParams['USE_PERMISSIONS'] && isset($GLOBALS['USER']) && is_object($GLOBALS['USER'])) {
            $arUserGroupArray = $USER->GetUserGroupArray();
            if (is_array($this->arParams['GROUP_PERMISSIONS']) && !empty($this->arParams['GROUP_PERMISSIONS'])) {
                foreach ($this->arParams['GROUP_PERMISSIONS'] as $PERM) {
                    if (in_array($PERM, $arUserGroupArray, true)) {
                        $bUSER_HAVE_ACCESS = true;
                        break;
                    }
                }
            }
        }
        
        return $bUSER_HAVE_ACCESS;
    }
    
    protected function setItems()
    {
        list($arSelect, $arFilter, $arSort, $bGetProperty) = $this->prepareGetListParams();
        
        $obParser                   = new CTextParser;
        $this->arResult['ITEMS']    = [];
        $this->arResult['ELEMENTS'] = [];
        $rsElement                  = CIBlockElement::GetList($arSort,
                                                              array_merge($arFilter, $this->arrFilter),
                                                              false,
                                                              $this->arNavParams,
                                                              $arSelect);
        $listPageUrlEl              = '';
        while ($obElement = $rsElement->GetNextElement()) {
            $arItem = $obElement->GetFields();
            if (empty($listPageUrlEl)) {
                $listPageUrlEl = $arItem['~LIST_PAGE_URL'];
            }
            
            $arButtons             = CIBlock::GetPanelButtons($arItem['IBLOCK_ID'],
                                                              $arItem['ID'],
                                                              0,
                                                              [
                                                                  'SECTION_BUTTONS' => false,
                                                                  'SESSID'          => false,
                                                              ]);
            $arItem['EDIT_LINK']   = $arButtons['edit']['edit_element']['ACTION_URL'];
            $arItem['DELETE_LINK'] = $arButtons['edit']['delete_element']['ACTION_URL'];
            
            if ($this->arParams['PREVIEW_TRUNCATE_LEN'] > 0) {
                $arItem['PREVIEW_TEXT'] =
                    $obParser->html_cut($arItem['PREVIEW_TEXT'], $this->arParams['PREVIEW_TRUNCATE_LEN']);
            }
            
            if (strlen($arItem['ACTIVE_FROM']) > 0) {
                $arItem['DISPLAY_ACTIVE_FROM'] =
                    CIBlockFormatProperties::DateFormat($this->arParams['ACTIVE_DATE_FORMAT'],
                                                        MakeTimeStamp($arItem['ACTIVE_FROM'],
                                                                      CSite::GetDateFormat()));
            } else {
                $arItem['DISPLAY_ACTIVE_FROM'] = '';
            }
            
            $ipropValues                =
                new Iblock\InheritedProperty\ElementValues($arItem['IBLOCK_ID'], $arItem['ID']);
            $arItem['IPROPERTY_VALUES'] = $ipropValues->getValues();
            
            Iblock\Component\Tools::getFieldImageData($arItem,
                                                      [
                                                          'PREVIEW_PICTURE',
                                                          'DETAIL_PICTURE',
                                                      ],
                                                      Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT);
            
            $arItem['FIELDS'] = [];
            if (is_array($this->arParams['FIELD_CODE']) && !empty($this->arParams['FIELD_CODE'])) {
                foreach ($this->arParams['FIELD_CODE'] as $code) {
                    if (array_key_exists($code, $arItem)) {
                        $arItem['FIELDS'][$code] = $arItem[$code];
                    }
                }
            }
            
            if ($bGetProperty) {
                $arItem['PROPERTIES'] = $obElement->GetProperties();
            }
            $arItem['DISPLAY_PROPERTIES'] = [];
            if (is_array($this->arParams['PROPERTY_CODE']) && !empty($this->arParams['PROPERTY_CODE'])) {
                foreach ($this->arParams['PROPERTY_CODE'] as $pid) {
                    $prop = &$arItem['PROPERTIES'][$pid];
                    if ((!is_array($prop['VALUE']) && !empty($prop['VALUE']))
                        || (is_array($prop['VALUE'])
                            && count($prop['VALUE']) > 0)) {
                        $arItem['DISPLAY_PROPERTIES'][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem,
                                                                                                       $prop,
                                                                                                       'news_out');
                    }
                }
            }
            
            if ($this->arParams['SET_LAST_MODIFIED']) {
                $time = DateTime::createFromUserTime($arItem['TIMESTAMP_X']);
                /** @noinspection PhpUndefinedMethodInspection */
                if (!isset($this->arResult['ITEMS_TIMESTAMP_X'])
                    || $time->getTimestamp() > $this->arResult['ITEMS_TIMESTAMP_X']->getTimestamp()) {
                    $this->arResult['ITEMS_TIMESTAMP_X'] = $time;
                }
            }
            
            $this->arResult['ITEMS'][]    = $arItem;
            $this->arResult['ELEMENTS'][] = $arItem['ID'];
        }
        
        $navComponentParameters = [];
        if ($this->arParams['PAGER_BASE_LINK_ENABLE'] === 'Y') {
            $pagerBaseLink = trim($this->arParams['PAGER_BASE_LINK']);
            if ($pagerBaseLink === '') {
                $pagerBaseLink = $listPageUrlEl;
            }
            
            if ($this->pagerParameters && isset($this->pagerParameters['BASE_LINK'])) {
                $pagerBaseLink = $this->pagerParameters['BASE_LINK'];
                unset($this->pagerParameters['BASE_LINK']);
            }
            
            $navComponentParameters['BASE_LINK'] =
                CHTTP::urlAddParams($pagerBaseLink, $this->pagerParameters, ['encode' => true]);
        }
        
        $this->arResult['NAV_STRING']      = $rsElement->GetPageNavStringEx($navComponentObject,
                                                                            $this->arParams['PAGER_TITLE'],
                                                                            $this->arParams['PAGER_TEMPLATE'],
                                                                            $this->arParams['PAGER_SHOW_ALWAYS'],
                                                                            $this,
                                                                            $navComponentParameters);
        $this->arResult['NAV_CACHED_DATA'] = null;
        $this->arResult['NAV_RESULT']      = $rsElement;
        $this->arResult['NAV_PARAM']       = $navComponentParameters;
        
        $this->setResultCacheKeys([
                                      'IBLOCK_TYPE_ID',
                                      'IBLOCK_ID',
                                      'NAV_CACHED_DATA',
                                      'ELEMENTS',
                                      'IPROPERTY_VALUES',
                                      'ITEMS_TIMESTAMP_X',
                                  ]);
    }
    
    /**
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    protected function checkModule() : bool
    {
        if (!Loader::includeModule('iblock')) {
            $this->abortResultCache();
            ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
            
            return false;
        }
        
        return true;
    }
    
    /**
     * @return array|bool
     */
    protected function prepareGetListParams()
    {
        //SELECT
        $arSelect     = array_merge($this->arParams['FIELD_CODE'],
                                    [
                                        'ID',
                                        'IBLOCK_ID',
                                        'IBLOCK_SECTION_ID',
                                        'NAME',
                                        'ACTIVE_FROM',
                                        'TIMESTAMP_X',
                                        'DETAIL_PAGE_URL',
                                        'LIST_PAGE_URL',
                                        'DETAIL_TEXT',
                                        'DETAIL_TEXT_TYPE',
                                        'PREVIEW_TEXT',
                                        'PREVIEW_TEXT_TYPE',
                                        'PREVIEW_PICTURE',
                                    ]);
        $bGetProperty = count($this->arParams['PROPERTY_CODE']) > 0;
        if ($bGetProperty) {
            $arSelect[] = 'PROPERTY_*';
        }
        //WHERE
        $arFilter = [
            'IBLOCK_ID'         => $this->arParams['IBLOCK_ID'],
            'IBLOCK_LID'        => SITE_ID,
            'ACTIVE'            => 'Y',
            'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS'] ? 'Y' : 'N',
        ];
        
        if ($this->arParams['CHECK_DATES']) {
            $arFilter['ACTIVE_DATE'] = 'Y';
        }
        
        $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
        //ORDER BY
        $arSort = [
            $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
            $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
        ];
        if (!array_key_exists('ID', $arSort)) {
            $arSort['ID'] = 'DESC';
        }
        
        return [
            $arSelect,
            $arFilter,
            $arSort,
            $bGetProperty,
        ];
    }
    
    protected function setIblocks()
    {
        $params = [
            'select' => [
                'ID',
                'CODE',
                'XML_ID',
                'IBLOCK_TYPE_ID',
                'NAME',
                'DESCRIPTION',
                'ACTIVE',
                'LIST_PAGE_URL',
            ],
            'filter' => ['ID' => $this->arParams['IBLOCK_ID']],
        ];
        $res    = Iblock\IblockTable::getList($params);
        while ($item = $res->fetch()) {
            //Handle List URL for Element, Section or IBlock
            $TEMPLATE = '';
            if (array_key_exists('LIST_PAGE_URL', $item)) {
                $TEMPLATE = $item['LIST_PAGE_URL'];
            }
            
            if (!empty($TEMPLATE)) {
                $res_tmp = [
                    'IBLOCK_ID'          => $item['ID'],
                    'IBLOCK_CODE'        => $item['CODE'],
                    'IBLOCK_EXTERNAL_ID' => $item['XML_ID'],
                ];
                
                $item['LIST_PAGE_URL_FORMATED'] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res_tmp, true);
                
            }
            $this->arResult['IBLOCKS'][$item['ID']] = $item;
        }
        //echo '<pre>', print_r($this->arResult['IBLOCKS'], true), '</pre>';
    }
}