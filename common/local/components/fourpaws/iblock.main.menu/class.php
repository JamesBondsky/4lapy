<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Class CFourPawsIBlockMainMenu
 * Компонент главного меню сайта, генерируемого по специальному инфоблоку
 *
 * @updated: 14.02.2018
 */

class CFourPawsIBlockMainMenu extends \CBitrixComponent {
    /** @var int $iMenuIBlockId */
    private $iMenuIBlockId = -1;
    /** @var int $iProductsIBlockId */
    private $iProductsIBlockId = -1;
    /** @var array $arMenuIBlockSectionsTree */
    private $arMenuIBlockSectionsTree = null;
    /** @var array $arMenuIBlockElements */
    private $arMenuIBlockElements = null;


    /**
     * @param \CBitrixComponent|null $obParentComponent
     */
    public function __construct($obParentComponent = null) {
        parent::__construct($obParentComponent);
    }

    /**
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams) {
        $arParams['MENU_IBLOCK_TYPE'] = isset($arParams['MENU_IBLOCK_TYPE']) && $arParams['MENU_IBLOCK_TYPE'] !== '' ? trim($arParams['MENU_IBLOCK_TYPE']) : 'menu';
        $arParams['MENU_IBLOCK_CODE'] = isset($arParams['MENU_IBLOCK_CODE']) && $arParams['MENU_IBLOCK_CODE'] !== '' ? trim($arParams['MENU_IBLOCK_CODE']) : 'main_menu';

        $arParams['PRODUCTS_IBLOCK_TYPE'] = isset($arParams['PRODUCTS_IBLOCK_TYPE']) ? trim($arParams['PRODUCTS_IBLOCK_TYPE']) : 'catalog';
        $arParams['PRODUCTS_IBLOCK_CODE'] = isset($arParams['PRODUCTS_IBLOCK_CODE']) ? trim($arParams['PRODUCTS_IBLOCK_CODE']) : 'products';

        $arParams['PRODUCTS_BRAND_PROP'] = isset($arParams['PRODUCTS_BRAND_PROP']) && $arParams['BRANDS_POPULAR_PROP'] !== '' ? trim($arParams['PRODUCTS_BRAND_PROP']) : 'BRAND';
        $arParams['BRANDS_POPULAR_PROP'] = isset($arParams['BRANDS_POPULAR_PROP']) && $arParams['BRANDS_POPULAR_PROP'] !== '' ? trim($arParams['BRANDS_POPULAR_PROP']) : 'POPULAR';
        $arParams['BRANDS_POPULAR_LIMIT'] = isset($arParams['BRANDS_POPULAR_LIMIT']) && (int)$arParams['BRANDS_POPULAR_LIMIT'] > 0 ? (int)$arParams['BRANDS_POPULAR_LIMIT'] : 6;

        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 43200;
        if ($arParams['CACHE_TYPE'] === 'N' || ($arParams['CACHE_TYPE'] === 'A' && \COption::GetOptionString('main', 'component_cache_on', 'Y') === 'N')) {
            $arParams['CACHE_TIME'] = 0;
        }

        $arParams['TEMPLATE_NO_CACHE'] = isset($arParams['TEMPLATE_NO_CACHE']) && $arParams['TEMPLATE_NO_CACHE'] === 'Y' ? 'Y' : 'N';

        $arParams['MAX_DEPTH_LEVEL'] = isset($arParams['MAX_DEPTH_LEVEL']) ? (int)$arParams['MAX_DEPTH_LEVEL'] : 4;

        return $arParams;
    }

    /**
     * @return array
     */
    public function executeComponent() {
        $arParams =& $this->arParams;
        $arResult =& $this->arResult;

        if ($arParams['MENU_IBLOCK_TYPE'] === '' || $arParams['MENU_IBLOCK_CODE'] === '') {
            return [];
        }

        $arGroups = [];

        $sCacheDir = SITE_ID.'/'.basename(__DIR__);
        $sCacheDir = '/'.ltrim($sCacheDir, '/');
        $sCachePath = $sCacheDir; 

        $sCacheId = md5(serialize([$arGroups, 'v2']));

        if ($this->startResultCache($arParams['CACHE_TIME'], $sCacheId, $sCachePath)) {
            if (!\Bitrix\Main\Loader::includeModule('iblock')) {
                $this->abortResultCache();
                return $arResult;
            }

            $arParams['MENU_IBLOCK_ID'] = $this->getMenuIBlockId();

            if ($arParams['MENU_IBLOCK_ID'] <= 0) {
                $this->abortResultCache();
                return $arResult;
            }

            $arResult['MENU_TREE'] = $this->getNestedMenu();

            $iProductsIBlockId = $this->getProductsIBlockId();
            $arResult['SECTIONS_POPULAR_BRANDS'] = [];
            if ($iProductsIBlockId) {
                foreach ($arResult['MENU_TREE'] as $arItem) {
                    if ($arItem['NESTED']) {
                        foreach ($arItem['NESTED'] as $arSubItem) {
                            if ($arSubItem['IS_BRAND_MENU']) {
                                continue;
                            }
                            //if (!$arSubItem['IS_DIR']) {
                            //    continue;
                            //}
                            if (!$arSubItem['SECTION_HREF']) {
                                continue;
                            }
                            if (!$arSubItem['NESTED']) {
                                continue;
                            }
                            if ($arSubItem['SECTION_HREF']['IBLOCK_ID'] == $iProductsIBlockId) {
                                $arResult['SECTIONS_POPULAR_BRANDS'][$arSubItem['SECTION_HREF']['ID']] = $this->getSectionPopularBrands(
                                    $arSubItem['SECTION_HREF']['ID'],
                                    $arParams['BRANDS_POPULAR_LIMIT']
                                );
                            }
                        }
                    }
                }
            }

            if ($arParams['TEMPLATE_NO_CACHE'] !== 'Y') {
                $this->includeComponentTemplate();
            }

            $this->endResultCache();
        }

        if ($arParams['TEMPLATE_NO_CACHE'] === 'Y') {
            $this->includeComponentTemplate();
            //$this->templateCachedData = $this->GetTemplateCachedData();
        }

        return $arResult;
    }

    /**
     * @return int
     */
    public function getMenuIBlockId() {
        if ($this->iMenuIBlockId < 0) {
            $this->iMenuIBlockId = $this->getIBlockIdByCode($this->arParams['MENU_IBLOCK_CODE'], $this->arParams['MENU_IBLOCK_TYPE']);
        }

        return $this->iMenuIBlockId;
    }

    /**
     * @return int
     */
    public function getProductsIBlockId() {
        if ($this->iProductsIBlockId < 0) {
            $this->iProductsIBlockId = $this->getIBlockIdByCode($this->arParams['PRODUCTS_IBLOCK_CODE'], $this->arParams['PRODUCTS_IBLOCK_TYPE']);
        }

        return $this->iProductsIBlockId;
    }

    /**
     * @return array
     */
    public function getNestedMenu() {
        $arData = [];
        $arSectionsTree = $this->getMenuIBlockSectionsTree();
        $arMenuElements = $this->getMenuIBlockElements();

        $arMenuIBlockElements2Sections = [];
        foreach ($arMenuElements as $arItem) {
            $arMenuIBlockElements2Sections[(int)$arItem['IBLOCK_SECTION_ID']][] = $arItem['ID'];
        }

        $iMaxDepthLevel = 0;
        foreach ($arSectionsTree as $iSectionId => $arSect) {
            $arSect['IS_DIR'] = true;
            $arSect['URL'] = $this->getMenuItemUrl($arSect);
            $arSect['NESTED'] = [];

            if ($arMenuIBlockElements2Sections[$iSectionId]) {
                foreach ($arMenuIBlockElements2Sections[$iSectionId] as $iElementId) {
                    $arElem = $arMenuElements[$iElementId];
                    $arElem['DEPTH_LEVEL'] = $arSect['DEPTH_LEVEL'] + 1;
                    $arElem['IS_DIR'] = false;
                    $arElem['URL'] = $this->getMenuItemUrl($arElem);
                    $arElem['NESTED'] = [];
                    $arSect['NESTED']['E'.$arElem['ID']] = $arElem;
                }
            }

            // пока все секции привязываем к корню, перенос по веткам будет выполнен ниже
            $arData['S'.$arSect['ID']] = $arSect;
            $iMaxDepthLevel = $arSect['DEPTH_LEVEL'] > $iMaxDepthLevel ? $arSect['DEPTH_LEVEL'] : $iMaxDepthLevel;
        }

        // заполнение вложенности секций, двигаемся сверху вниз (от максимального уровня вложенности)
        $iCurLevel = $iMaxDepthLevel;
        while ($iCurLevel > 1) {
            foreach ($arData as $mKey => $arItem) {
                if (!$arItem['IS_DIR']) {
                    continue;
                }
                if ($arItem['DEPTH_LEVEL'] != $iCurLevel) {
                    continue;
                }
                if ($arItem['IBLOCK_SECTION_ID'] && isset($arData['S'.$arItem['IBLOCK_SECTION_ID']])) {
                    $arData['S'.$arItem['IBLOCK_SECTION_ID']]['NESTED']['S'.$arItem['ID']] = $arItem;
                    unset($arData[$mKey]);
                }
            }
            --$iCurLevel;
        }

        if (!empty($arMenuIBlockElements2Sections[0])) {
            foreach ($arMenuIBlockElements2Sections[0] as $iElementId) {
                $arElem = $arMenuElements[$iElementId];
                $arElem['DEPTH_LEVEL'] = 1;
                $arElem['IS_DIR'] = false;
                $arElem['URL'] = $this->getMenuItemUrl($arElem);
                $arElem['NESTED'] = [];
                $arData['E'.$arElem['ID']] = $arElem;
            }
        }

        $arData = $this->sortRecursive($arData);

        return $arData;
    }

    /**
     * @param array
     * @return string
     */
    protected function getMenuItemUrl($arItem) {
        $sReturn = '';
        if ($arItem['HREF'] !== '') {
            $sReturn = $arItem['HREF'];
        } elseif ($arItem['ELEMENT_HREF'] && $arItem['ELEMENT_HREF']['URL'] !== '') {
            $sReturn = $arItem['ELEMENT_HREF']['URL'];
        } elseif ($arItem['SECTION_HREF'] && $arItem['SECTION_HREF']['URL'] !== '') {
            $sReturn = $arItem['SECTION_HREF']['URL'];
        }

        return $sReturn;
    }

    /**
     * @param array $arData
     * @return array
     */
    protected function sortRecursive($arData) {
        $iIdx = 0;
        foreach ($arData as &$arItem) {
            if ($arItem['NESTED']) {
                $arItem['NESTED'] = $this->sortRecursive($arItem['NESTED']);
            }
            // формируем поле для сортировки: DEPTH_LEVEL-SORT-IS_DIR-IDX
            $arTmp = [];
            $arTmp[] = $arItem['DEPTH_LEVEL'];
            $arTmp[] = $arItem['SORT'];
            // секциям отдаем больший вес
            $arTmp[] = $arItem['IS_DIR'] ? '0' : '1';
            // чтобы сохранялась исходная последовательность среди равных
            $arTmp[] = ++$iIdx;
            $arItem['SORT_IDX'] = implode('-', $arTmp);
        }
        unset($arItem);

        uasort(
            $arData,
            function($arA, $arB) {
                return strnatcmp($arA['SORT_IDX'], $arB['SORT_IDX']);
            }
        );

        return $arData;
    }

    /**
     * @param string $sIBlockCode
     * @param string $sIBlockType
     * @return int
     */
    protected function getIBlockIdByCode($sIBlockCode, $sIBlockType = '') {
        $iReturn = 0;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return $iReturn;
        }

        $arFilter = [
            'CHECK_PERMISSIONS' => 'N',
            'CODE' => $sIBlockCode,
            'SITE_ID' => SITE_ID,
        ];
        if ($sIBlockType !== '') {
            $arFilter['TYPE'] = $sIBlockType;
        }
        $arIBlock = \CIBlock::GetList(['ID' => 'ASC'], $arFilter)->fetch();
        $iReturn = $arIBlock ? $arIBlock['ID'] : 0;

        return $iReturn;
    }

    /**
     * @return array
     */
    public function getMenuIBlockSectionsTree() {
        if (is_null($this->arMenuIBlockSectionsTree)) {
            $this->obtainMenuIBlockSectionsTree();
        }

        return $this->arMenuIBlockSectionsTree;
    }

    /**
     * @return void
     */
    protected function obtainMenuIBlockSectionsTree() {
        $this->arMenuIBlockSectionsTree = [];

        // здесь делается подключение модуля инфоблоков
        $iIBlockId = $this->getMenuIBlockId();

        if (!$iIBlockId) {
            return;
        }

        $arRelElements = [];
        $arRelSections = [];
        $dbItems = \CIBlockSection::GetList(
            [
                'LEFT_MARGIN' => 'ASC' // !!!
            ],
            [
                'IBLOCK_ID' => $iIBlockId,
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y',
                '<=DEPTH_LEVEL' => $this->arParams['MAX_DEPTH_LEVEL'],
            ],
            false,
            [
                'ID', 'NAME', 'IBLOCK_SECTION_ID',
                'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL',
                'SORT',
                'CODE', 'XML_ID',
                'UF_*',
            ]
        );
        while ($arItem = $dbItems->getNext(true, false)) {
            $this->arMenuIBlockSectionsTree[$arItem['ID']] = [
                'ID' => $arItem['ID'],
                'CODE' => $arItem['CODE'],
                'XML_ID' => $arItem['XML_ID'],
                'NAME' => $arItem['NAME'],
                'IBLOCK_SECTION_ID' => (int)$arItem['IBLOCK_SECTION_ID'],
                'SORT' => $arItem['SORT'],
                'DEPTH_LEVEL' => $arItem['DEPTH_LEVEL'],
                'HREF' => isset($arItem['UF_HREF']) ? trim($arItem['UF_HREF']) : '',
                'ELEMENT_HREF_ID' => 0,
                'ELEMENT_HREF' => [], // заполняются ниже
                'SECTION_HREF_ID' => isset($arItem['UF_SECTION_HREF']) ? (int)$arItem['UF_SECTION_HREF'] : 0,
                'SECTION_HREF' => [], // заполняются ниже
                'TARGET_BLANK' => isset($arItem['UF_TARGET_BLANK']) ? (int)$arItem['UF_TARGET_BLANK'] : 0,
                'IS_BRAND_MENU'  => isset($arItem['UF_BRAND_MENU']) ? (int)$arItem['UF_BRAND_MENU'] : 0,
            ];

            if ($this->arMenuIBlockSectionsTree[$arItem['ID']]['ELEMENT_HREF_ID'] > 0) {
                $arRelElements[$this->arMenuIBlockSectionsTree[$arItem['ID']]['ELEMENT_HREF_ID']][] = $arItem['ID'];
            }
            if ($this->arMenuIBlockSectionsTree[$arItem['ID']]['SECTION_HREF_ID'] > 0) {
                $arRelSections[$this->arMenuIBlockSectionsTree[$arItem['ID']]['SECTION_HREF_ID']][] = $arItem['ID'];
            }
        }

        // заполнение данными связанных элементов
        $this->arMenuIBlockSectionsTree = $this->completeRelElements($this->arMenuIBlockSectionsTree, $arRelElements);

        // заполнение данными связанных секций
        $this->arMenuIBlockSectionsTree = $this->completeRelSections($this->arMenuIBlockSectionsTree, $arRelSections);
    }

    /**
     * @return array
     */
    public function getMenuIBlockElements() {
        if (is_null($this->arMenuIBlockElements)) {
            $this->obtainMenuIBlockElements();
        }
        return $this->arMenuIBlockElements;
    }

    /**
     * @return void
     */
    protected function obtainMenuIBlockElements() {
        $this->arMenuIBlockElements = [];

        // здесь делается подключение модуля инфоблоков
        $iIBlockId = $this->getMenuIBlockId();

        if (!$iIBlockId) {
            return;
        }

        $arRelElements = [];
        $arRelSections = [];
        $dbItems = \CIBlockElement::GetList(
            [
                'SORT' => 'ASC',
                'ID' => 'ASC',
            ],
            [
                'IBLOCK_ID' => $iIBlockId,
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
            ],
            false,
            false,
            [
                'ID', 'NAME', 'IBLOCK_ID', 'IBLOCK_SECTION_ID',
                'SORT',
                'CODE', 'XML_ID',
                'PROPERTY_HREF',
                'PROPERTY_ELEMENT_HREF',
                'PROPERTY_SECTION_HREF',
                'PROPERTY_TARGET_BLANK',
            ]
        );
        while ($arItem = $dbItems->getNext(true, false)) {
            $this->arMenuIBlockElements[$arItem['ID']] = [
                'ID' => $arItem['ID'],
                'CODE' => $arItem['CODE'],
                'XML_ID' => $arItem['XML_ID'],
                'NAME' => $arItem['NAME'],
                'IBLOCK_SECTION_ID' => (int)$arItem['IBLOCK_SECTION_ID'],
                'SORT' => $arItem['SORT'],
                'HREF' => isset($arItem['PROPERTY_HREF_VALUE']) ? trim($arItem['PROPERTY_HREF_VALUE']) : '',
                'ELEMENT_HREF_ID' => isset($arItem['PROPERTY_ELEMENT_HREF_VALUE']) ? (int)$arItem['PROPERTY_ELEMENT_HREF_VALUE'] : 0,
                'ELEMENT_HREF' => [], // заполняются ниже
                'SECTION_HREF_ID' => isset($arItem['PROPERTY_SECTION_HREF_VALUE']) ? (int)$arItem['PROPERTY_SECTION_HREF_VALUE'] : 0,
                'SECTION_HREF' => [], // заполняются ниже
                'TARGET_BLANK' => isset($arItem['PROPERTY_TARGET_BLANK_VALUE']) ? (int)$arItem['PROPERTY_TARGET_BLANK_VALUE'] : 0,
            ];

            if ($this->arMenuIBlockElements[$arItem['ID']]['ELEMENT_HREF_ID'] > 0) {
                $arRelElements[$this->arMenuIBlockElements[$arItem['ID']]['ELEMENT_HREF_ID']][] = $arItem['ID'];
            }
            if ($this->arMenuIBlockElements[$arItem['ID']]['SECTION_HREF_ID'] > 0) {
                $arRelSections[$this->arMenuIBlockElements[$arItem['ID']]['SECTION_HREF_ID']][] = $arItem['ID'];
            }
        }

        // заполнение данными связанных элементов
        $this->arMenuIBlockElements = $this->completeRelElements($this->arMenuIBlockElements, $arRelElements);

        // заполнение данными связанных секций
        $this->arMenuIBlockElements = $this->completeRelSections($this->arMenuIBlockElements, $arRelSections);
    }


    /**
     * @param array $arData
     * @param array $arRelElements
     * @return array
     */
    private function completeRelElements($arData, $arRelElements) {
        if ($arRelElements) {
            $dbItems = \CIBlockElement::GetList(
                [],
                [
                    'ID' => array_keys($arRelElements),
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                ],
                false,
                false,
                [
                    'ID', 'DETAIL_PAGE_URL',
                ]
            );
            while ($arItem = $dbItems->getNext(true, false)) {
                if ($arRelElements[$arItem['ID']]) {
                    foreach ($arRelElements[$arItem['ID']] as $iTmpId) {
                        if ($arData[$iTmpId]) {
                            $arData[$iTmpId]['ELEMENT_HREF'] = [
                                'ID' => $arItem['ID'],
                                'URL' => trim($arItem['DETAIL_PAGE_URL']),
                                'IBLOCK_ID' => $arItem['IBLOCK_ID'],
                                'IBLOCK_CODE' => $arItem['IBLOCK_CODE'],
                                //'IBLOCK_SECTION_ID' => $arItem['IBLOCK_SECTION_ID'],
                                //'CODE' => $arItem['CODE'],
                            ];
                        }
                    }
                }
            }
        }

        return $arData;
    }

    /**
     * @param array $arData
     * @param array $arRelSections
     * @return array
     */
    private function completeRelSections($arData, $arRelSections) {
        if ($arRelSections) {
            $dbItems = \CIBlockSection::GetList(
                [],
                [
                    'ID' => array_keys($arRelSections),
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y',
                ],
                false,
                [
                    'ID', 'IBLOCK_ID', 'SECTION_PAGE_URL',
                ]
            );
            while ($arItem = $dbItems->getNext(true, false)) {
                if ($arRelSections[$arItem['ID']]) {
                    foreach ($arRelSections[$arItem['ID']] as $iTmpId) {
                        if ($arData[$iTmpId]) {
                            $arData[$iTmpId]['SECTION_HREF'] = [
                                'ID' => $arItem['ID'],
                                'URL' => trim($arItem['SECTION_PAGE_URL']),
                                'IBLOCK_ID' => $arItem['IBLOCK_ID'],
                                'IBLOCK_CODE' => $arItem['IBLOCK_CODE'],
                                //'IBLOCK_SECTION_ID' => $arItem['IBLOCK_SECTION_ID'],
                                //'CODE' => $arItem['CODE'],
                            ];
                        }
                    }
                }
            }
        }

        return $arData;
    }

    /**
     * @param int $iSectionId
     * @param int $iLimit
     * @return array
     */
    protected function getSectionPopularBrands($iSectionId, $iLimit = 10) {
        $arReturn = [];

        $iIBlockId = $this->getProductsIBlockId();

        if (!$iIBlockId) {
            return $arReturn;
        }

        $sBrandPropCode = $this->arParams['PRODUCTS_BRAND_PROP'];
        $sPopularPropCode = $this->arParams['BRANDS_POPULAR_PROP'];
        if ($sBrandPropCode === '' || $sPopularPropCode === '') {
            return $arReturn;
        }

        $sBrandPropField = 'PROPERTY_'.$sBrandPropCode;
        $sPopularPropField = 'PROPERTY_'.$sPopularPropCode;
        $sPopularPropFieldFull = $sBrandPropField.'.'.$sPopularPropField;

        $arFilter = [
            'IBLOCK_ID' => $iIBlockId,
            'SECTION_ID' => $iSectionId,
            'INCLUDE_SUBSECTIONS' => 'Y',
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            $sBrandPropField.'.ACTIVE' => 'Y',
            $sBrandPropField.'.ACTIVE_DATE' => 'Y',
        ];
        $arSelectBase = [
            $sBrandPropField,
            $sPopularPropFieldFull,
            $sBrandPropField.'.NAME',
            $sBrandPropField.'.DETAIL_PAGE_URL',
            $sBrandPropField.'.DETAIL_PICTURE',
        ];
        $arGroupBy = $arSelectBase;

        $dbItems = \CIBlockElement::GetList(
            [
                $sPopularPropFieldFull => 'DESC',
                $sBrandPropField.'.SORT' => 'ASC',
                $sBrandPropField.'.NAME' => 'ASC',
            ],
            $arFilter,
            $arGroupBy,
            [
                'nTopCount' => $iLimit,
            ],
            array_merge(
                $arSelectBase,
                array('IBLOCK_ID')
            )
        );
        while ($arItem = $dbItems->getNext(true, false)) {
            if (!$arItem[$sBrandPropField.'_'.$sPopularPropField.'_VALUE']) {
                continue;
            }
            $arReturn[$arItem[$sBrandPropField.'_VALUE']] = [
                'ID' => $arItem[$sBrandPropField.'_VALUE'],
                'NAME' => $arItem[$sBrandPropField.'_NAME'],
                'DETAIL_PAGE_URL' => $arItem[$sBrandPropField.'_DETAIL_PAGE_URL'],
                'DETAIL_PICTURE' => $arItem[$sBrandPropField.'_DETAIL_PICTURE'],
            ];
        }

        return $arReturn;
    }
}
