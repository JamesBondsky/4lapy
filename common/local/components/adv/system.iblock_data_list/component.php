<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}
/**
 * Системный мета-компонент выборки элементов инфоблоков
 *
 * @author Sergey Leshchenko, 2011
 * rev.: 21.04.2017 (DD.MM.YYYY)
 */

$arResult = array();
if (empty($arParams['IBLOCKS']) && empty($arParams['IBLOCK_CODES'])) {
    return;
}
if (!strlen(trim($arParams['ELEMENT_FILTER_NAME']))) {
    $arExtElementFilter = array();
} elseif (!preg_match('#^[A-Za-z_][A-Za-z01-9_]*$#', $arParams['ELEMENT_FILTER_NAME'])) {
    $arExtElementFilter = array();
} else {
    $arExtElementFilter = isset($GLOBALS[$arParams['ELEMENT_FILTER_NAME']]) ? $GLOBALS[$arParams['ELEMENT_FILTER_NAME']] : array();
    if (!is_array($arExtElementFilter)) {
        $arExtElementFilter = array();
    }
}

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 43200;
}
if ($arParams['CACHE_TYPE'] == 'N' || ($arParams['CACHE_TYPE'] == 'A' && \COption::GetOptionString('main', 'component_cache_on', 'Y') == 'N')) {
    $arParams['CACHE_TIME'] = 0;
}
$arParams['INCLUDE_TEMPLATE'] = isset($arParams['INCLUDE_TEMPLATE']) && $arParams['INCLUDE_TEMPLATE'] == 'Y' ? 'Y' : 'N';
$arParams['CACHE_TEMPLATE'] = isset($arParams['CACHE_TEMPLATE']) && $arParams['CACHE_TEMPLATE'] == 'Y' ? 'Y' : 'N';
$arParams['CACHE_GROUPS'] = isset($arParams['CACHE_GROUPS']) && $arParams['CACHE_GROUPS'] == 'N' ? 'N' : 'Y';
$arParams['CACHE_EMPTY_RESULT'] = isset($arParams['CACHE_EMPTY_RESULT']) && $arParams['CACHE_EMPTY_RESULT'] == 'Y' ? 'Y' : 'N';
$arParams['GET_DISPLAY_PROPERTIES'] = isset($arParams['GET_DISPLAY_PROPERTIES']) && $arParams['GET_DISPLAY_PROPERTIES'] === 'Y' ? 'Y' : 'N';
$arParams['GET_INHERITED_PROPERTIES'] = isset($arParams['GET_INHERITED_PROPERTIES']) && $arParams['GET_INHERITED_PROPERTIES'] === 'Y' ? 'Y' : 'N';
$arParams['GET_EDIT_LINKS'] = isset($arParams['GET_EDIT_LINKS']) && $arParams['GET_EDIT_LINKS'] === 'Y' ? 'Y' : 'N';
if ($arParams['GET_EDIT_LINKS'] === 'Y') {
    $arParams['CACHE_GROUPS'] = 'Y';
}

// подготовим параметры постраничной навигации
$mNavParams = false;
$mCacheNavigation = false;
$arParams['ELEMENT_CNT'] = !empty($arParams['ELEMENT_CNT']) ? intval($arParams['ELEMENT_CNT']) : 0;
if ($arParams['ELEMENT_CNT'] > 0) {
    $arParams['PAGER_DESC_NUMBERING'] = isset($arParams['PAGER_DESC_NUMBERING']) && $arParams['PAGER_DESC_NUMBERING'] == 'Y';
    $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] = isset($arParams['PAGER_DESC_NUMBERING_CACHE_TIME']) ? intval($arParams['PAGER_DESC_NUMBERING_CACHE_TIME']) : '0';
    if (isset($arParams['PAGER_SHOW']) && $arParams['PAGER_SHOW'] == 'Y') {
        if (isset($arParams['NAV_PAGE_IN_SESSION']) && $arParams['NAV_PAGE_IN_SESSION'] === 'Y') {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'Y');
        } else {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }
        $arParams['nPageWindow'] = isset($arParams['nPageWindow']) && intval($arParams['nPageWindow']) > 0 ? intval($arParams['nPageWindow']) : false;
        $arParams['PAGER_SHOW_ALL'] = isset($arParams['PAGER_SHOW_ALL']) && $arParams['PAGER_SHOW_ALL'] == 'Y';
        $arParams['PAGER_TITLE'] = isset($arParams['PAGER_TITLE']) ? trim($arParams['PAGER_TITLE']) : '';
        $arParams['PAGER_SHOW_ALWAYS'] = isset($arParams['PAGER_SHOW_ALWAYS']) && $arParams['PAGER_SHOW_ALWAYS'] != 'N';
        $arParams['PAGER_TEMPLATE'] = isset($arParams['PAGER_TEMPLATE']) ? trim($arParams['PAGER_TEMPLATE']) : '';
        $mNavParams = array(
            'nPageSize' => $arParams['ELEMENT_CNT'], 
            'bDescPageNumbering' => $arParams['PAGER_DESC_NUMBERING']
        );
        $mCacheNavigation = \CDBResult::GetNavParams($mNavParams, $arParams['PAGER_SHOW_ALL']);
        if ($mCacheNavigation['PAGEN'] == 0 && $arParams['CACHE_TIME'] && $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] > 0) {
            $arParams['CACHE_TIME'] = $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'];
        }
    } else {
        $mNavParams = array(
            'nTopCount' => $arParams['ELEMENT_CNT'],
            'bDescPageNumbering' => $arParams['PAGER_DESC_NUMBERING']
        );
    }
} else {
    $arParams['ELEMENT_CNT'] = 0;
}

$arAddCacheParams = array();

$sCacheDir = !empty($arParams['CACHE_DIR']) ? $arParams['CACHE_DIR'] : SITE_ID.'/system.iblock_data_list';
$sCacheDir = rtrim($sCacheDir, '/').'/';
$sCacheDir = '/'.ltrim($sCacheDir, '/');
$sCachePath = $sCacheDir; 

$arGroups = $arParams['CACHE_GROUPS'] == 'Y' ? $GLOBALS['USER']->GetGroups() : array();

$sCacheId = md5(serialize(array($arAddCacheParams, $arGroups, $arExtElementFilter, $mCacheNavigation)));
if ($this->StartResultCache($arParams['CACHE_TIME'], $sCacheId, $sCachePath)) {
    if (!\CModule::IncludeModule('iblock')) {
        $this->AbortResultCache();
        return;
    }

    //
    // Параметры, которые не должны приниматься из запросов браузера
    //
    $arOrder = array();
    $arParams['SORT_BY1'] = trim($arParams['SORT_BY1']);
    if (!empty($arParams['SORT_BY1'])) {
        $arParams['SORT_ORDER1'] = $arParams['SORT_ORDER1'] != 'DESC' ? 'ASC' : 'DESC';
        $arOrder[$arParams['SORT_BY1']] = $arParams['SORT_ORDER1'];
    }
    $arParams['SORT_BY2'] = trim($arParams['SORT_BY2']);
    if (!empty($arParams['SORT_BY2'])) {
        $arParams['SORT_ORDER2'] = $arParams['SORT_ORDER2'] != 'DESC' ? 'ASC' : 'DESC';
        $arOrder[$arParams['SORT_BY2']] = $arParams['SORT_ORDER2'];
    }

    $arParams['FIELD_CODE'] = !is_array($arParams['FIELD_CODE']) ? array() : $arParams['FIELD_CODE'];
    $arSelect = $arParams['FIELD_CODE'];
    if (empty($arSelect)) {
        $arSelect = array(
            'ID',
            'IBLOCK_ID',
            'CODE',
            'IBLOCK_SECTION_ID',
            'NAME'
        );
    }
    if (($arParams['GET_INHERITED_PROPERTIES'] === 'Y' || $arParams['GET_EDIT_LINKS'] === 'Y') && !in_array('IBLOCK_ID', $arSelect)) {
        $arSelect[] = 'IBLOCK_ID';
    }

    $arFilter = array();
    if (!empty($arParams['IBLOCK_CODES'])) {
        // ! с типом проверки фильтра "=" выборка может работать неправильно !
        //$arFilter['=IBLOCK_CODE'] = $arParams['IBLOCK_CODES'];
        $arFilter['IBLOCK_CODE'] = $arParams['IBLOCK_CODES'];
    }
    if (!empty($arParams['IBLOCKS'])) {
        $arFilter['IBLOCK_ID'] = $arParams['IBLOCKS'];
    }
    $arFilter['ACTIVE'] = 'Y';
    $arFilter = array_merge($arFilter, $arExtElementFilter);
    if (isset($arParams['CHECK_PERMISSIONS']) && $arParams['CHECK_PERMISSIONS'] == 'Y') {
        $arFilter['CHECK_PERMISSIONS'] = 'Y';
    }
    if (isset($arParams['CHECK_DATES']) && $arParams['CHECK_DATES'] === 'Y') {
        $arFilter['ACTIVE_DATE'] = 'Y';
    }
    $arGroupBy = !empty($arParams['GROUP_BY']) && is_array($arParams['GROUP_BY']) ? $arParams['GROUP_BY'] : false;
    if (!empty($arGroupBy)) {
        $arSelect = array();
    }

    $arParams['KEY_FIELD'] = isset($arParams['KEY_FIELD']) ? trim($arParams['KEY_FIELD']) : '';
    $bCustomKey = !empty($arParams['KEY_FIELD']);
    $bExactFields = !empty($arParams['EXACT_FIELDS']) && $arParams['EXACT_FIELDS'] == 'Y';

    //
    // Выборка элементов
    //
    $dbItems = \CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $mNavParams, $arSelect);
    if ($mCacheNavigation) {
        if (isset($arParams['PAGER_SHOW_ALL'])) {
            $dbItems->bShowAll = $arParams['PAGER_SHOW_ALL'];
        }
        if (!empty($arParams['nPageWindow'])) {
            $dbItems->nPageWindow = $arParams['nPageWindow'];
        }
    }

    $bGetOb = isset($arParams['GET_NEXT_ELEMENT_MODE']) && $arParams['GET_NEXT_ELEMENT_MODE'] == 'Y';
    $bGetUseTilda = isset($arParams['GET_NEXT_USE_TILDA']) && $arParams['GET_NEXT_USE_TILDA'] == 'Y';
    while(true) {
        $arItem = array();
        if ($bGetOb) {
            $obItem = $dbItems->GetNextElement(true, $bGetUseTilda);
            if ($obItem) {
                $arItem = $obItem->GetFields();
                $arItem['PROPERTIES'] = $obItem->GetProperties();
                if ($arParams['GET_DISPLAY_PROPERTIES'] === 'Y') {
                    foreach($arItem['PROPERTIES'] as $sPtopCode => $arPropData) {
                        $arItem['DISPLAY_PROPERTIES'][$sPtopCode] = \CIBlockFormatProperties::GetDisplayValue($arItem, $arPropData, '');
                    }
                }
            }
        } else {
            $arItem = $dbItems->GetNext(true, $bGetUseTilda);
        }
        if ($arItem) {
            // информация о картинках из полей элемента
            if (array_key_exists('PREVIEW_PICTURE', $arItem)) {
                $arItem['PREVIEW_PICTURE'] = \CFile::GetFileArray($arItem['PREVIEW_PICTURE']);
            }

            // информация о картинках из полей элемента
            if (array_key_exists('DETAIL_PICTURE', $arItem)) {
                $arItem['DETAIL_PICTURE'] = \CFile::GetFileArray($arItem['DETAIL_PICTURE']);
            }

            if ($arParams['GET_INHERITED_PROPERTIES'] === 'Y') {
                $obIPropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem['IBLOCK_ID'], $arItem['ID']);
                $arItem['IPROPERTY_VALUES'] = $obIPropValues->GetValues();
            }

            if ($arParams['GET_EDIT_LINKS'] === 'Y') {
                $arButtons = \CIBlock::GetPanelButtons(
                    $arItem['IBLOCK_ID'],
                    $arItem['ID'],
                    0,
                    array(
                        'SECTION_BUTTONS' => false,
                        'SESSID' => false
                    )
                );
                $arItem['EDIT_LINK'] = $arButtons['edit']['edit_element']['ACTION_URL'];
                $arItem['DELETE_LINK'] = $arButtons['edit']['delete_element']['ACTION_URL'];
            }

            if ($bExactFields) {
                $arTmp = array();
                foreach($arSelect as $sKey) {
                    $arTmp[$sKey] = isset($arItem[$sKey]) ? $arItem[$sKey] : '';
                }
                $arItem = $arTmp;
            }

            if ($bCustomKey) {
                // кастомный ключ
                $arResult['ITEMS'][$arItem[$arParams['KEY_FIELD']]] = $arItem;
            } else {
                $arResult['ITEMS'][] = $arItem;
            }
        } else {
            break;
        }
    }

    if ($mCacheNavigation) {
        // не будем подключать постраничку, если выборка меньше
        $bGetNavString = true;
        if ($arParams['ELEMENT_CNT']) {
            if (($dbItems->SelectedRowsCount() < $arParams['ELEMENT_CNT']) && (!$arParams['PAGER_SHOW_ALWAYS'])) {
                $bGetNavString = false;
            }
        }
        if ($bGetNavString) {
            $arResult['NAV_STRING'] = $dbItems->GetPageNavStringEx($obNavComponentObject, $arParams['PAGER_TITLE'], $arParams['PAGER_TEMPLATE'], $arParams['PAGER_SHOW_ALWAYS']);
            $arResult['NAV_CACHED_DATA'] = $obNavComponentObject->GetTemplateCachedData();
            $arResult['NAV_RESULT'] = $dbItems;
            $arResult['NAV_RESULT']->arResult = false;
        }
    }

    $bCache = false;
    if (!empty($arResult['ITEMS']) || $arParams['CACHE_EMPTY_RESULT'] == 'Y') {
        $bCache = true;
    }

    if (!$bCache) {
        $this->AbortResultCache();
    }

    if ($arParams['INCLUDE_TEMPLATE'] == 'Y' && $arParams['CACHE_TEMPLATE'] == 'Y') {
        $this->IncludeComponentTemplate();
    } elseif ($bCache) {
        $this->EndResultCache();
    }
}

if ($arParams['INCLUDE_TEMPLATE'] == 'Y' && $arParams['CACHE_TEMPLATE'] != 'Y') {
    $this->IncludeComponentTemplate();
}

return $arResult;
