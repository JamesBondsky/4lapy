<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 23.11.2017
 * Time: 16:08
 */
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
class CNewsFullComponent extends CBitrixComponent
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
        CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        
        if (!isset($arParams['CACHE_TIME'])) {
            $arParams['CACHE_TIME'] = 36000000;
        }
        
        $arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
        if (strlen($arParams['IBLOCK_TYPE']) <= 0) {
            $arParams['IBLOCK_TYPE'] = 'news';
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
        $arParams['PARENT_SECTION']      = (int)$arParams['PARENT_SECTION'];
        $arParams['INCLUDE_SUBSECTIONS'] = $arParams['INCLUDE_SUBSECTIONS'] !== 'N';
        $arParams['SET_LAST_MODIFIED']   = $arParams['SET_LAST_MODIFIED'] === 'Y';
        
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
        
        $arParams['DETAIL_URL'] = trim($arParams['DETAIL_URL']);
        
        $arParams['NEWS_COUNT'] = (int)$arParams['NEWS_COUNT'];
        if ($arParams['NEWS_COUNT'] <= 0) {
            $arParams['NEWS_COUNT'] = 20;
        }
        
        $arParams['CACHE_FILTER'] = $arParams['CACHE_FILTER'] === 'Y';
        
        $arParams['SET_TITLE']                 = $arParams['SET_TITLE'] !== 'N';
        $arParams['SET_BROWSER_TITLE']         =
            (isset($arParams['SET_BROWSER_TITLE']) && $arParams['SET_BROWSER_TITLE'] === 'N' ? 'N' : 'Y');
        $arParams['SET_META_KEYWORDS']         =
            (isset($arParams['SET_META_KEYWORDS']) && $arParams['SET_META_KEYWORDS'] === 'N' ? 'N' : 'Y');
        $arParams['SET_META_DESCRIPTION']      =
            (isset($arParams['SET_META_DESCRIPTION']) && $arParams['SET_META_DESCRIPTION'] === 'N' ? 'N' : 'Y');
        $arParams['ADD_SECTIONS_CHAIN']        = $arParams['ADD_SECTIONS_CHAIN'] !== 'N'; //Turn on by default
        $arParams['INCLUDE_IBLOCK_INTO_CHAIN'] = $arParams['INCLUDE_IBLOCK_INTO_CHAIN'] !== 'N';
        $arParams['STRICT_SECTION_CHECK']      =
            (isset($arParams['STRICT_SECTION_CHECK']) && $arParams['STRICT_SECTION_CHECK'] === 'Y');
        $arParams['ACTIVE_DATE_FORMAT']        = trim($arParams['ACTIVE_DATE_FORMAT']);
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
                                                              $this->arParams['PARENT_SECTION'],
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
            
            $this->setMeta($arTitleOptions);
            
            $this->addChainItem();
            
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
        $rsElement->SetUrlTemplates($this->arParams['DETAIL_URL'], '', $this->arParams['IBLOCK_URL']);
        $listPageUrlEl = '';
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
                if ($this->arResult['SECTION']
                    && $this->arResult['SECTION']['PATH']
                    && $this->arResult['SECTION']['PATH'][0]
                    && $this->arResult['SECTION']['PATH'][0]['~SECTION_PAGE_URL']) {
                    $pagerBaseLink = $this->arResult['SECTION']['PATH'][0]['~SECTION_PAGE_URL'];
                } elseif (!empty($listPageUrlEl)) {
                    $pagerBaseLink = $listPageUrlEl;
                }
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
                                      'ID',
                                      'IBLOCK_TYPE_ID',
                                      'LIST_PAGE_URL',
                                      'NAV_CACHED_DATA',
                                      'NAME',
                                      'SECTION',
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
     * @param $arTitleOptions
     */
    protected function setMeta($arTitleOptions)
    {
        global $APPLICATION;
        if ($this->arParams['SET_TITLE']) {
            if ($this->arResult['IPROPERTY_VALUES']
                && $this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] !== '') {
                $APPLICATION->SetTitle($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $arTitleOptions);
            } elseif (isset($this->arResult['NAME'])) {
                $APPLICATION->SetTitle($this->arResult['NAME'], $arTitleOptions);
            }
        }
        
        if ($this->arResult['IPROPERTY_VALUES']) {
            if ($this->arParams['SET_BROWSER_TITLE'] === 'Y'
                && $this->arResult['IPROPERTY_VALUES']['SECTION_META_TITLE'] !== '') {
                $APPLICATION->SetPageProperty('title',
                                              $this->arResult['IPROPERTY_VALUES']['SECTION_META_TITLE'],
                                              $arTitleOptions);
            }
            
            if ($this->arParams['SET_META_KEYWORDS'] === 'Y'
                && $this->arResult['IPROPERTY_VALUES']['SECTION_META_KEYWORDS'] !== '') {
                $APPLICATION->SetPageProperty('keywords',
                                              $this->arResult['IPROPERTY_VALUES']['SECTION_META_KEYWORDS'],
                                              $arTitleOptions);
            }
            
            if ($this->arParams['SET_META_DESCRIPTION'] === 'Y'
                && $this->arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'] !== '') {
                $APPLICATION->SetPageProperty('description',
                                              $this->arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'],
                                              $arTitleOptions);
            }
        }
    }
    
    protected function addChainItem()
    {
        global $APPLICATION;
        if ($this->arParams['INCLUDE_IBLOCK_INTO_CHAIN'] && isset($this->arResult['NAME'])) {
            if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['SECTION'])) {
                $APPLICATION->AddChainItem($this->arResult['NAME'],
                                           strlen($this->arParams['IBLOCK_URL'])
                                           > 0 ? $this->arParams['IBLOCK_URL'] : $this->arResult['LIST_PAGE_URL']);
            } else {
                $APPLICATION->AddChainItem($this->arResult['NAME']);
            }
        }
        
        if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['SECTION']['PATH'])
            && !empty($this->arResult['SECTION']['PATH'])) {
            foreach ($this->arResult['SECTION']['PATH'] as $arPath) {
                if ($arPath['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] !== '') {
                    $APPLICATION->AddChainItem($arPath['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'],
                                               $arPath['~SECTION_PAGE_URL']);
                } else {
                    $APPLICATION->AddChainItem($arPath['NAME'], $arPath['~SECTION_PAGE_URL']);
                }
            }
        }
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
        
        $PARENT_SECTION = CIBlockFindTools::GetSectionID($this->arParams['PARENT_SECTION'],
                                                         $this->arParams['PARENT_SECTION_CODE'],
                                                         [
                                                             'GLOBAL_ACTIVE' => 'Y',
                                                             'IBLOCK_ID'     => $this->arResult['ID'],
                                                         ]);
        
        if ($this->arParams['STRICT_SECTION_CHECK']
            && ($this->arParams['PARENT_SECTION'] > 0
                || strlen($this->arParams['PARENT_SECTION_CODE']) > 0)) {
            if ($PARENT_SECTION <= 0) {
                $this->abortResultCache();
                Iblock\Component\Tools::process404(trim($this->arParams['MESSAGE_404']) ?: GetMessage('T_NEWS_NEWS_NA'),
                                                   true,
                                                   $this->arParams['SET_STATUS_404'] === 'Y',
                                                   $this->arParams['SHOW_404'] === 'Y',
                                                   $this->arParams['FILE_404']);
                
                return false;
            }
        }
        
        $this->arParams['PARENT_SECTION'] = $PARENT_SECTION;
        
        if ($this->arParams['PARENT_SECTION'] > 0) {
            $arFilter['SECTION_ID'] = $this->arParams['PARENT_SECTION'];
            if ($this->arParams['INCLUDE_SUBSECTIONS']) {
                $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
            }
            
            $this->arResult['SECTION'] = ['PATH' => []];
            $rsPath                    =
                CIBlockSection::GetNavChain($this->arResult['ID'], $this->arParams['PARENT_SECTION']);
            $rsPath->SetUrlTemplates('', $this->arParams['SECTION_URL'], $this->arParams['IBLOCK_URL']);
            while ($arPath = $rsPath->GetNext()) {
                $ipropValues                         =
                    new Iblock\InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $arPath['ID']);
                $arPath['IPROPERTY_VALUES']          = $ipropValues->getValues();
                $this->arResult['SECTION']['PATH'][] = $arPath;
            }
            
            $ipropValues                        =
                new Iblock\InheritedProperty\SectionValues($this->arResult['ID'], $this->arParams['PARENT_SECTION']);
            $this->arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
        } else {
            $this->arResult['SECTION'] = false;
        }
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