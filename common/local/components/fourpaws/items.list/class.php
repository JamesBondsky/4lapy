<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

use Bitrix\Iblock;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Context;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

/** @noinspection AutoloadingIssuesInspection */
class CItemsListComponent extends CBitrixComponent
{
    protected $externalFilter = [];
    
    protected $navParams;
    
    protected $pagerParameters;
    
    /**
     * {@inheritdoc}
     */
    public function onPrepareComponentParams($params) : array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }
        
        if (is_array($params['IBLOCK_ID'])) {
            foreach ($params['IBLOCK_ID'] as $key => $id) {
                if (!is_numeric($id) || (int)$id <= 0) {
                    unset($params['IBLOCK_ID'][$key]);
                }
            }
        } elseif (is_numeric($params['IBLOCK_ID']) && (int)$params['IBLOCK_ID'] > 0) {
            $params['IBLOCK_ID'] = (int)$params['IBLOCK_ID'];
        }
        if (empty($params['IBLOCK_ID'])) {
            /**Получение инфоблоков если не установлены*/
            
            
            $cache = Cache::createInstance();
            if ($cache->initCache(
                $params['CACHE_TIME'],
                serialize(
                    [
                        'IBLOCK_TYPE' => $params['IBLOCK_TYPE'],
                        'TYPE'        => 'full_iblocks',
                    ]
                ),
                'items_list'
            )) {
                $vars                = $cache->getVars();
                $params['IBLOCK_ID'] = $vars['IBLOCK_ID'];
            } elseif ($cache->startDataCache()) {
                try {
                    $paramsIblock = [
                        'select' => ['ID'],
                        'filter' => ['ACTIVE' => 'Y'],
                    ];
                    if (!empty($params['IBLOCK_TYPE'])) {
                        $paramsIblock['filter']['IBLOCK_TYPE_ID'] = $params['IBLOCK_TYPE'];
                    }
                    $res = IblockTable::getList($paramsIblock);
                    while ($item = $res->fetch()) {
                        $params['IBLOCK_ID'][] = (int)$item['ID'];
                    }
                } catch (\Bitrix\Main\ArgumentException $e) {
                }
                
                $cache->endDataCache(['IBLOCK_ID' => $params['IBLOCK_ID']]);
            }
        }
        $params['SET_LAST_MODIFIED'] = $params['SET_LAST_MODIFIED'] === 'Y';
        
        $params['SORT_BY1'] = trim($params['SORT_BY1']);
        if (strlen($params['SORT_BY1']) <= 0) {
            $params['SORT_BY1'] = 'ACTIVE_FROM';
        }
        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $params['SORT_ORDER1'])) {
            $params['SORT_ORDER1'] = 'DESC';
        }
        
        if (strlen($params['SORT_BY2']) <= 0) {
            $params['SORT_BY2'] = 'SORT';
        }
        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $params['SORT_ORDER2'])) {
            $params['SORT_ORDER2'] = 'ASC';
        }
        
        $params['CHECK_DATES'] = $params['CHECK_DATES'] !== 'N';
        
        if (!is_array($params['FIELD_CODE'])) {
            $params['FIELD_CODE'] = [];
        }
        foreach ($params['FIELD_CODE'] as $key => $val) {
            if (!$val) {
                unset($params['FIELD_CODE'][$key]);
            }
        }
        
        if (!is_array($params['PROPERTY_CODE'])) {
            $params['PROPERTY_CODE'] = [];
        }
        foreach ($params['PROPERTY_CODE'] as $key => $val) {
            if ($val === '') {
                unset($params['PROPERTY_CODE'][$key]);
            }
        }
        
        $params['NEWS_COUNT'] = (int)$params['NEWS_COUNT'];
        if ($params['NEWS_COUNT'] <= 0) {
            $params['NEWS_COUNT'] = 20;
        }
        
        $params['CACHE_FILTER'] = $params['CACHE_FILTER'] === 'Y';
        
        $params['ACTIVE_DATE_FORMAT'] = trim($params['ACTIVE_DATE_FORMAT']);
        if (strlen($params['ACTIVE_DATE_FORMAT']) <= 0) {
            
            $params['ACTIVE_DATE_FORMAT'] = Date::getFormat();
        }
        $params['PREVIEW_TRUNCATE_LEN'] = (int)$params['PREVIEW_TRUNCATE_LEN'];
        
        $params['DISPLAY_TOP_PAGER']               = $params['DISPLAY_TOP_PAGER'] === 'Y';
        $params['DISPLAY_BOTTOM_PAGER']            = $params['DISPLAY_BOTTOM_PAGER'] !== 'N';
        $params['PAGER_TITLE']                     = trim($params['PAGER_TITLE']);
        $params['PAGER_SHOW_ALWAYS']               = $params['PAGER_SHOW_ALWAYS'] === 'Y';
        $params['PAGER_TEMPLATE']                  = trim($params['PAGER_TEMPLATE']);
        $params['PAGER_DESC_NUMBERING']            = $params['PAGER_DESC_NUMBERING'] === 'Y';
        $params['PAGER_DESC_NUMBERING_CACHE_TIME'] = (int)$params['PAGER_DESC_NUMBERING_CACHE_TIME'];
        $params['PAGER_SHOW_ALL']                  = $params['PAGER_SHOW_ALL'] === 'Y';
        $params['CHECK_PERMISSIONS']               = $params['CHECK_PERMISSIONS'] !== 'N';
        
        return $params;
    }
    
    /**
     * {@inheritdoc}
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        global $USER, $APPLICATION;
        
        $this->setFrameMode(true);
        
        $this->arResult['IBLOCK_ID']      = $this->arParams['IBLOCK_ID'];
        $this->arResult['IBLOCK_TYPE_ID'] = $this->arParams['IBLOCK_TYPE'];
        
        $this->setFilter();
        list($this->navParams, $navigation, $this->pagerParameters) = $this->setPageParams();
        
        $userHaveAccess = $this->checkPermission($USER);
        
        if ($this->startResultCache(
            
            false,
            [
                $this->arParams['CACHE_GROUPS'] === 'N' ? false : $USER->GetGroups(),
                $userHaveAccess,
                $navigation,
                $this->externalFilter,
                $this->pagerParameters,
            ]
        
        )) {
            $res = $this->checkModule();
            if (!$res) {
                return $res;
            }
            
            $this->arResult = [];
            
            $this->setIblocks();
            
            $this->arResult['USER_HAVE_ACCESS'] = $userHaveAccess;
            
            $this->setItems();
            
            $this->includeComponentTemplate();
        }
        
        if (isset($this->arResult['ID'])) {
            
            if ($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas() && Loader::includeModule('iblock')) {
                
                $buttons = CIBlock::GetPanelButtons(
                    $this->arResult['ID'],
                    0,
                    0,
                    ['SECTION_BUTTONS' => false]
                );
                
                /** @noinspection NotOptimalIfConditionsInspection */
                if ($APPLICATION->GetShowIncludeAreas()) {
                    
                    $this->addIncludeAreaIcons(
                        CIBlock::GetComponentMenu(
                            $APPLICATION->GetPublicShowMode(),
                            $buttons
                        )
                    );
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
            || !preg_match(
                '/^[A-Za-z_][A-Za-z01-9_]*$/',
                $this->arParams['FILTER_NAME']
            )) {
            $this->externalFilter = [];
        } else {
            $this->externalFilter = $GLOBALS[$this->arParams['FILTER_NAME']];
            if (!is_array($this->externalFilter)) {
                $this->externalFilter = [];
            }
        }
        
        if (!$this->arParams['CACHE_FILTER'] && count($this->externalFilter) > 0) {
            $this->arParams['CACHE_TIME'] = 0;
        }
    }
    
    /**
     * @return array
     */
    protected function setPageParams() : array
    {
        if ($this->arParams['DISPLAY_TOP_PAGER'] || $this->arParams['DISPLAY_BOTTOM_PAGER']) {
            $navParams = [
                'nPageSize'          => $this->arParams['NEWS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll'           => $this->arParams['PAGER_SHOW_ALL'],
            ];
            
            $navigation = CDBResult::GetNavParams($navParams);
            if ($navigation['PAGEN'] === 0 && $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] > 0) {
                $this->arParams['CACHE_TIME'] = $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'];
            }
        } else {
            $navParams  = [
                'nTopCount'          => $this->arParams['NEWS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
            ];
            $navigation = false;
        }
        
        if (empty($this->arParams['PAGER_PARAMS_NAME'])
            || !preg_match(
                '/^[A-Za-z_][A-Za-z01-9_]*$/',
                $this->arParams['PAGER_PARAMS_NAME']
            )) {
            $pagerParameters = [];
        } else {
            $pagerParameters = $GLOBALS[$this->arParams['PAGER_PARAMS_NAME']];
            if (!is_array($pagerParameters)) {
                $pagerParameters = [];
            }
        }
        
        return [
            $navParams,
            $navigation,
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
        
        $userHaveAccess = !$this->arParams['USE_PERMISSIONS'];
        if ($this->arParams['USE_PERMISSIONS'] && isset($GLOBALS['USER']) && is_object($GLOBALS['USER'])) {
            $userGroup = $USER->GetUserGroupArray();
            if (is_array($this->arParams['GROUP_PERMISSIONS']) && !empty($this->arParams['GROUP_PERMISSIONS'])) {
                foreach ($this->arParams['GROUP_PERMISSIONS'] as $perm) {
                    if (in_array($perm, $userGroup, true)) {
                        $userHaveAccess = true;
                        break;
                    }
                }
            }
        }
        
        return $userHaveAccess;
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
    
    protected function setIblocks()
    {
        try {
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
            $res = IblockTable::getList($params);
            while ($item = $res->fetch()) {
                $template = '';
                if (array_key_exists('LIST_PAGE_URL', $item)) {
                    $template = $item['LIST_PAGE_URL'];
                }
        
                if (!empty($template)) {
                    $resTmp = [
                        'IBLOCK_ID'          => $item['ID'],
                        'IBLOCK_CODE'        => $item['CODE'],
                        'IBLOCK_EXTERNAL_ID' => $item['XML_ID'],
                    ];
            
                    
                    $item['LIST_PAGE_URL_FORMATED'] = CIBlock::ReplaceDetailUrl($template, $resTmp, true);
                }
                $this->arResult['IBLOCKS'][$item['ID']] = $item;
            }
        } catch (\Bitrix\Main\ArgumentException $e) {
        }
        
    }
    
    protected function setItems()
    {
        list($select, $filter, $sort, $getProperty) = $this->prepareGetListParams();
        
        
        $obParser                   = new CTextParser;
        $this->arResult['ITEMS']    = [];
        $this->arResult['ELEMENTS'] = [];
        
        $rsElement     = CIBlockElement::GetList(
            $sort,
            array_merge($filter, $this->externalFilter),
            false,
            $this->navParams,
            $select
        );
        $listPageUrlEl = '';
        while ($obElement = $rsElement->GetNextElement()) {
            $item = $obElement->GetFields();
            if (empty($listPageUrlEl)) {
                $listPageUrlEl = $item['~LIST_PAGE_URL'];
            }
            
            
            $buttons             = CIBlock::GetPanelButtons(
                $item['IBLOCK_ID'],
                $item['ID'],
                0,
                [
                    'SECTION_BUTTONS' => false,
                    'SESSID'          => false,
                ]
            );
            $item['EDIT_LINK']   = $buttons['edit']['edit_element']['ACTION_URL'];
            $item['DELETE_LINK'] = $buttons['edit']['delete_element']['ACTION_URL'];
            
            if ($this->arParams['PREVIEW_TRUNCATE_LEN'] > 0) {
                $item['PREVIEW_TEXT'] =
                    $obParser->html_cut($item['PREVIEW_TEXT'], $this->arParams['PREVIEW_TRUNCATE_LEN']);
            }
            
            if (strlen($item['ACTIVE_FROM']) > 0) {
                
                $item['DISPLAY_ACTIVE_FROM'] = CIBlockFormatProperties::DateFormat(
                    $this->arParams['ACTIVE_DATE_FORMAT'],
                    MakeTimeStamp(
                        $item['ACTIVE_FROM'],
                        CSite::GetDateFormat()
                    )
                );
            } else {
                $item['DISPLAY_ACTIVE_FROM'] = '';
            }
            
            
            $ipropValues              = new Iblock\InheritedProperty\ElementValues($item['IBLOCK_ID'], $item['ID']);
            $item['IPROPERTY_VALUES'] = $ipropValues->getValues();
            
            
            Tools::getFieldImageData(
                $item,
                [
                    'PREVIEW_PICTURE',
                    'DETAIL_PICTURE',
                ],
                Tools::IPROPERTY_ENTITY_ELEMENT
            );
            
            $item['FIELDS'] = [];
            if (is_array($this->arParams['FIELD_CODE']) && !empty($this->arParams['FIELD_CODE'])) {
                foreach ($this->arParams['FIELD_CODE'] as $code) {
                    if (array_key_exists($code, $item)) {
                        $item['FIELDS'][$code] = $item[$code];
                    }
                }
            }
            
            if ($getProperty) {
                $item['PROPERTIES'] = $obElement->GetProperties();
            }
            $item['DISPLAY_PROPERTIES'] = [];
            if (is_array($this->arParams['PROPERTY_CODE']) && !empty($this->arParams['PROPERTY_CODE'])) {
                foreach ($this->arParams['PROPERTY_CODE'] as $pid) {
                    $prop = &$item['PROPERTIES'][$pid];
                    if ((!is_array($prop['VALUE']) && !empty($prop['VALUE']))
                        || (is_array($prop['VALUE'])
                            && count($prop['VALUE']) > 0)) {
                        
                        $item['DISPLAY_PROPERTIES'][$pid] = CIBlockFormatProperties::GetDisplayValue(
                            $item,
                            $prop,
                            'news_out'
                        );
                    }
                }
            }
            
            if ($this->arParams['SET_LAST_MODIFIED']) {
                
                $time = DateTime::createFromUserTime($item['TIMESTAMP_X']);
                /** @noinspection PhpUndefinedMethodInspection */
                if (!isset($this->arResult['ITEMS_TIMESTAMP_X'])
                    || $time->getTimestamp() > $this->arResult['ITEMS_TIMESTAMP_X']->getTimestamp()) {
                    $this->arResult['ITEMS_TIMESTAMP_X'] = $time;
                }
            }
            
            $this->arResult['ITEMS'][]    = $item;
            $this->arResult['ELEMENTS'][] = $item['ID'];
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
        
        $this->arResult['NAV_STRING']      = $rsElement->GetPageNavStringEx(
            
            $navComponentObject,
            $this->arParams['PAGER_TITLE'],
            $this->arParams['PAGER_TEMPLATE'],
            $this->arParams['PAGER_SHOW_ALWAYS'],
            $this,
            $navComponentParameters
        
        );
        $this->arResult['NAV_CACHED_DATA'] = null;
        $this->arResult['NAV_RESULT']      = $rsElement;
        $this->arResult['NAV_PARAM']       = $navComponentParameters;
        
        $this->setResultCacheKeys(
            [
                'IBLOCK_TYPE_ID',
                'IBLOCK_ID',
                'NAV_CACHED_DATA',
                'ELEMENTS',
                'IPROPERTY_VALUES',
                'ITEMS_TIMESTAMP_X',
            ]
        );
    }
    
    /**
     * @return array|bool
     */
    protected function prepareGetListParams()
    {
        $select      = array_merge(
            $this->arParams['FIELD_CODE'],
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
            ]
        );
        $getProperty = count($this->arParams['PROPERTY_CODE']) > 0;
        if ($getProperty) {
            $select[] = 'PROPERTY_*';
        }
        $filter = [
            'IBLOCK_ID'         => $this->arParams['IBLOCK_ID'],
            'IBLOCK_LID'        => SITE_ID,
            'ACTIVE'            => 'Y',
            'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS'] ? 'Y' : 'N',
        ];
        
        if ($this->arParams['CHECK_DATES']) {
            $filter['ACTIVE_DATE'] = 'Y';
        }
        
        $filter['INCLUDE_SUBSECTIONS'] = 'Y';
        $sort                          = [
            $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
            $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
        ];
        if (!array_key_exists('ID', $sort)) {
            $sort['ID'] = 'DESC';
        }
        
        return [
            $select,
            $filter,
            $sort,
            $getProperty,
        ];
    }
}
