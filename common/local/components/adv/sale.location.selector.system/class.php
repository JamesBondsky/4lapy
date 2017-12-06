<?php

use Bitrix\Main;
use Bitrix\Main\DB;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\GroupTable;
use Bitrix\Sale\Location\LocationTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Bitrix Framework
 *
 * @package    bitrix
 * @subpackage sale
 * @copyright  2001-2014 Bitrix
 */

CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.search');

Loc::loadMessages(__FILE__);
/** @noinspection PhpUndefinedClassInspection */

/** @noinspection AutoloadingIssuesInspection */
class CBitrixLocationSelectorSystemComponentPropLocation extends CBitrixLocationSelectorSearchComponent
{
    const ID_BLOCK_LEN         = 90;
    
    const HUGE_TAIL_LEN        = 30;
    
    const PAGE_SIZE            = 10;
    
    const LOCATION_ENTITY_NAME = LocationTable::class;
    
    protected $entityClass = false;
    
    protected $useGroups   = true;
    
    protected $useCodes    = false;
    
    //protected $dbResult = array();
    
    private $locationsFromRequest = false;
    
    private $groupsFromRequest    = false;
    
    public static function processGetPathRequest($parameters)
    {
        $idList = $parameters['ITEMS'];
        
        if (!is_array($idList) || empty($idList)) {
            throw new Main\SystemException('Empty array passed');
        } // todo: assert here later
        
        $result = [];
        
        $idList = array_unique($idList);
        $items  = [];
        
        $res = self::getEntityListByListOfPrimary(
            self::LOCATION_ENTITY_NAME,
            $idList,
            [
                'select' => [
                    'ID',
                    'LEFT_MARGIN',
                    'RIGHT_MARGIN',
                ],
            ]
        );
        while ($item = $res->fetch()) {
            $items[] = $item;
        }
        
        if (empty($items)) {
            return $result;
        }
        
        $result = static::getPathToNodesV2($items);
        
        return $result;
    }
    
    /**
     * Returns a list of items by supplying a set of their IDs or CODEs
     *
     * @param string                  $entityClass
     * @param string[]|integer[]|null $list
     * @param mixed[]                 $parameters
     * @param string                  $fieldCode identify what type of linking is used. Only two of legal values
     *                                           allowed: ID and CODE
     *
     * @return Bitrix\Main\DB\ArrayResult $result
     */
    protected static function getEntityListByListOfPrimary(
        $entityClass,
        $list = [],
        array $parameters = [],
        $fieldCode = 'ID'
    ) : \Bitrix\Main\DB\ArrayResult
    {
        $result = [];
        
        if (is_array($list) && !empty($list)) {
            $block = [];
            $cnt   = count($list);
            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0, $j = 0; $i < $cnt; $i++, $j++) {
                if ($j === self::ID_BLOCK_LEN) {
                    $parameters['filter']['=' . $fieldCode] = $block;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $res = $entityClass::getList($parameters);
                    /** @noinspection PhpUndefinedMethodInspection */
                    while ($item = $res->fetch()) {
                        $result[] = $item;
                    }
                    
                    $block = [];
                    $j     = 0;
                }
                
                $block[] = $list[$i];
            }
            
            if (!empty($block)) {
                $parameters['filter']['=' . $fieldCode] = $block;
                /** @noinspection PhpUndefinedMethodInspection */
                $res = $entityClass::getList($parameters);
                /** @noinspection PhpUndefinedMethodInspection */
                while ($item = $res->fetch()) {
                    $result[] = $item;
                }
            }
        }
        
        return new DB\ArrayResult($result);
    }
    
    protected static function processSearchRequestV2GetFinderBehaviour()
    {
        return ['USE_INDEX' => false];
    }
    
    protected static function processSearchRequestV2GetAdditional(&$data, $parameters)
    {
        if (!empty($data['ITEMS']) && is_array($parameters['additionals'])) {
            if (in_array('PATH', $parameters['additionals'], true)) {
                // show path to each found node
                static::processSearchRequestV2GetAdditionalPathNodes($data, $parameters);
            }
            
            // show common count of items by current filter
            if (is_array($parameters['filter']) && in_array('CNT_BY_FILTER', $parameters['additionals'], true)) {
                $item                         = Location\LocationTable::getList(
                    [
                        'select' => ['CNT'],
                        'filter' => $parameters['filter'],
                    ]
                )->fetch();
                $data['ETC']['CNT_BY_FILTER'] = $item['CNT'];
            }
            
            // show parent item in case of PARENT_ID condition in filter
            if (in_array('PARENT_ITEM', $parameters['additionals'], true)) {
                $id = false;
                if ((int)$parameters['filter']['=PARENT_ID']) {
                    $id = (int)$parameters['filter']['=PARENT_ID'];
                } elseif ((int)$parameters['filter']['PARENT_ID']) {
                    $id = (int)$parameters['filter']['PARENT_ID'];
                }
                
                if ($id !== false) {
                    $path                      = [];
                    $data['ETC']['PATH_ITEMS'] = [];
                    
                    $res = Location\LocationTable::getPathToNode(
                        $id,
                        [
                            'select' => [
                                'VALUE'   => 'ID',
                                'CODE',
                                'TYPE_ID',
                                'DISPLAY' => 'NAME.NAME',
                            ],
                            'filter' => [
                                '=NAME.LANGUAGE_ID' => ''
                                                       !== $parameters['filter']['=NAME.LANGUAGE_ID'] ? $parameters['filter']['=NAME.LANGUAGE_ID'] : LANGUAGE_ID,
                            ],
                        ]
                    );
                    
                    $node = [];
                    while ($item = $res->fetch()) {
                        $path[]                                    = (int)$item['VALUE'];
                        $data['ETC']['PATH_ITEMS'][$item['VALUE']] = $item;
                        
                        $node = $item;
                    }
                    
                    $node['PATH']               = array_reverse($path);
                    $data['ETC']['PARENT_ITEM'] = $node;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
     *
     * @param mixed[] $params List of unchecked parameters
     *
     * @return mixed[] Checked and valid parameters
     */
    public function onPrepareComponentParams($params) : array
    {
        //$arParams = parent::onPrepareComponentParams($arParams);
        
        self::tryParseString($params['LINK_ENTITY_NAME']);
        self::tryParseString($params['INPUT_NAME']);
        self::tryParseString($params['ENTITY_PRIMARY']);
        self::tryParseString($params['ENTITY_VARIABLE_NAME'], 'id');
        self::tryParseInt($params['CACHE_TIME'], false, true);
        self::tryParseString($params['EDIT_MODE_SWITCH'], 'loc_selector_mode');
        
        return $params;
    }
    
        protected function obtainCachedData(&$cachedData)
    {
        $this->obtainDataLocationTypes($cachedData);
        $this->obtainDataGroups($cachedData);
        $this->obtainDataLevelOneLocations($cachedData);
    }/** @noinspection MoreThanThreeArgumentsInspection */

protected function obtainDataLocationTypes(&$cachedData)
    {
        $types               = Location\Admin\TypeHelper::getTypes(LANGUAGE_ID);
        $cachedData['TYPES'] = [];
        foreach ($types as $type) {
            $type['NAME'] = $type['NAME_CURRENT'];
            unset($type['NAME_CURRENT']);
            $cachedData['TYPES'][$type['ID']] = $type;
        }
    }
    
    /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */
    
    protected function obtainDataGroups(&$cachedData)
    {
        $groups = [];
        
        if ($this->useGroups) {
            $res = Location\GroupTable::getList(
                [
                    'select' => [
                        'ID',
                        'CODE',
                        'LNAME' => 'NAME.NAME',
                    ],
                    'filter' => ['NAME.LANGUAGE_ID' => LANGUAGE_ID],
                ]
            );
            $res->addReplacedAliases(['LNAME' => 'NAME']);
            while ($item = $res->fetch()) {
                $item['ID']          = (int)$item['ID'];
                $groups[$item['ID']] = $item;
            }
        }
        
        $cachedData['GROUPS'] = $groups;
    }
    
    // override for do-nothing
    
    protected function obtainDataLevelOneLocations(&$cachedData)
    {
        // here we require a tag cache
        
        $res = Location\LocationTable::getList(
            [
                'filter' => [
                    'PARENT_ID'        => 0,
                    'NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                'select' => [
                    'LNAME' => 'NAME.NAME',
                    'CODE',
                    'ID',
                    'CHILD_CNT',
                ],
            ]
        );
        $res->addReplacedAliases(['LNAME' => 'NAME']);
        $cachedData['LOCATIONS'] = [];
        while ($item = $res->fetch()) {
            $cachedData['LOCATIONS'][] = [
                'ID'        => $item['ID'],
                'NAME'      => $item['NAME'],
                'IS_PARENT' => $item['CHILD_CNT'] > 0,
            ];
        }
    }
    
    protected function obtainCacheDependentData()
    {
        $this->obtainDataAdditional();
        $this->obtainDataConnectors();
    }
    
    protected function obtainDataConnectors()
    {
        if (!$this->arParams['LINK_ENTITY_NAME'] && $this->arParams['PROP_LOCATION'] !== 'Y') {
            $this->errors['FATAL'][] = Loc::getMessage('SALE_SLSS_LINK_ENTITY_NAME_NOT_SET');
            
            return;
        }
        
        $class      = $this->entityClass;
        $parameters = [
            'select' => [
                'ID',
                'CODE',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
                'SORT',
                'TYPE_ID',
                'LNAME' => 'NAME.NAME',
            ],
            'filter' => [
                'NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
        ];
        
        $linkFld = $this->useCodes ? 'CODE' : 'ID';
        
        $res    = false;
        $points = [];
        
        // get locations to display
        if ($this->arParams['PROP_LOCATION'] === 'Y') {
            $res = self::getEntityListByListOfPrimary(
                self::LOCATION_ENTITY_NAME,
                $this->locationsFromRequest,
                $parameters,
                $linkFld
            );
        } else {
            if ($this->locationsFromRequest !== false) { // get from request when form save fails or smth
                $res = self::getEntityListByListOfPrimary(
                    self::LOCATION_ENTITY_NAME,
                    $this->locationsFromRequest,
                    $parameters,
                    $linkFld
                );
            } elseif ('' !== $this->arParams['ENTITY_PRIMARY']) { // get from database, if entity exists
                /** @noinspection PhpUndefinedMethodInspection */
                $res = $class::getConnectedLocations($this->arParams['ENTITY_PRIMARY'], $parameters);
            }
        }
        
        if ($res !== false) {
            $res->addReplacedAliases(['LNAME' => 'NAME']);
            
            while ($item = $res->fetch()) {
                $points[$item['ID']] = $item;
            }
        }
        
        if (!empty($points)) {
            // same algorythm repeated on client side - fetch PATH for only visible items
            $pointsToGetPath = $points;
            if ((count($points) - static::PAGE_SIZE) > static::HUGE_TAIL_LEN) {
                $pointsToGetPath = array_slice($points, 0, static::PAGE_SIZE);
            }
            
            try {
                $res = Location\LocationTable::getPathToMultipleNodes(
                    $pointsToGetPath,
                    [
                        'select' => [
                            'LNAME' => 'NAME.NAME',
                        ],
                        'filter' => [
                            'NAME.LANGUAGE_ID' => LANGUAGE_ID,
                        ],
                    ]
                );
                
                while ($item = $res->fetch()) {
                    $item['ID'] = (int)$item['ID'];
                    
                    if (!is_array($item['PATH']) || empty($item['PATH'])) {
                        // we got empty PATH. This is not a normal case, item without a path is not sutable for displaying. Skip.
                        unset($points[$item['ID']]);
                    } else {
                        foreach ($item['PATH'] as &$node) {
                            $node['NAME'] = $node['LNAME'];
                            unset($node['LNAME']);
                        }
                        unset($node);
                        $points[$item['ID']]['PATH'] = $item['PATH'];
                    }
                }
            } catch (\Bitrix\Main\ArgumentException $e) {
                LocationHelper::informAdminLocationDatabaseFailure();
            }
            
            // clean up some fields
            foreach ($points as $i => &$location) {
                unset($location['LEFT_MARGIN'], $location['RIGHT_MARGIN']); // system fields should not figure in $arResult
                // same
            }
            unset($location);
        }
        
        $this->dbResult['CONNECTIONS']['LOCATION'] = $points;
        
        if ($this->useGroups) {
            $parameters = [
                'select' => [
                    'ID',
                    'CODE',
                    'LNAME' => 'NAME.NAME',
                ],
                'filter' => [
                    'NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
            ];
            
            $res    = false;
            $points = [];
            
            if ($this->arParams['PROP_LOCATION'] === 'Y') {
                $res = self::getEntityListByListOfPrimary(
                    GroupTable::class,
                    $this->groupsFromRequest,
                    $parameters,
                    $linkFld
                );
            } else {
                if ($this->groupsFromRequest !== false) {
                    $res = self::getEntityListByListOfPrimary(
                        GroupTable::class,
                        $this->groupsFromRequest,
                        $parameters,
                        $linkFld
                    );
                } elseif ('' !== $this->arParams['ENTITY_PRIMARY']) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $res = $class::getConnectedGroups($this->arParams['ENTITY_PRIMARY'], $parameters);
                }
            }
            
            if ($res !== false) {
                $res->addReplacedAliases(['LNAME' => 'NAME']);
                
                while ($item = $res->fetch()) {
                    $item['ID']          = (int)$item['ID'];
                    $points[$item['ID']] = $item;
                }
            }
            
            $this->dbResult['CONNECTIONS']['GROUP'] = $points;
        }
    }
    
    protected function obtainDataLocation()
    {
    }
    
    protected function checkParameters()
    {
        /** @noinspection PhpUndefinedClassInspection */
        $result = parent::checkParameters();
        
        if ($this->arParams['PROP_LOCATION'] === 'Y') {
            $this->useGroups = true;
            $this->useCodes  = false;
        } else {
            if (!$this->arParams['LINK_ENTITY_NAME']) {
                $this->errors['FATAL'][] = Loc::getMessage('SALE_SLSS_ENTITY_PRIMARY_NOT_SET');
                
                return false;
            }
            
            $this->entityClass = $this->arParams['LINK_ENTITY_NAME'] . 'Table';
            if (!class_exists($this->entityClass)) {
                $this->errors['FATAL'][] = Loc::getMessage('SALE_SLSS_LINK_ENTITY_CLASS_UNKNOWN');
                
                return false;
            }
            
            $class = $this->entityClass;
            
            $isInstace = false;
            try {
                $a         = new $class();
                $isInstace = ($a instanceof \Bitrix\Sale\Location\Connector);
            } catch (\Exception $e) {
            }
            
            if (!$isInstace) {
                $this->errors['FATAL'][] = Loc::getMessage('SALE_SLSS_WRONG_LINK_CLASS');
                
                return false;
            }
            
            /** @noinspection PhpUndefinedMethodInspection */
            $this->useGroups = $class::getUseGroups();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->useCodes = $class::getUseCodes();
        }
        
        // selected in request
        if (is_array($this->arParams['SELECTED_IN_REQUEST']['L'])) {
            $this->locationsFromRequest =
                static::normalizeList($this->arParams['SELECTED_IN_REQUEST']['L'], !$this->useCodes);
        }
        
        if (is_array($this->arParams['SELECTED_IN_REQUEST']['G'])) {
            $this->groupsFromRequest =
                static::normalizeList($this->arParams['SELECTED_IN_REQUEST']['G'], !$this->useCodes);
        }
        
        return $result;
    }
    
    protected static function normalizeList($list, $expectNumeric = true)
    {
        $list = array_unique(array_values($list));
        foreach ($list as $i => $id) {
            if ($expectNumeric) {
                if ((int)$id !== $id) {
                    unset($list[$i]);
                }
                
                $list[$i] = (int)$id;
                if (!$list[$i]) {
                    unset($list[$i]);
                }
            } else {
                if ('' === $list[$i]) {
                    unset($list[$i]);
                }
            }
        }
        
        return $list;
    }
    
    protected function getCacheDependences()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return array_merge(parent::getCacheDependences(), [self::getStrForVariable($this->useGroups)]);
    }
    
    /**
     * Move data read from database to a specially formatted $arResult
     *
     * @return void
     */
    protected function formatResult()
    {
        $this->arResult           =& $this->dbResult;
        $this->arResult['ERRORS'] =& $this->errors;
        
        $this->arResult['RANDOM_TAG'] = random_int(999, 99999) . random_int(999, 99999) . random_int(999, 99999);
        
        $this->arResult['USE_GROUPS'] = $this->useGroups;
        $this->arResult['USE_CODES']  = $this->useCodes;
    }
}
