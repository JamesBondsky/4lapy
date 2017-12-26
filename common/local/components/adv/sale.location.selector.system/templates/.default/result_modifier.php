<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// prepare data for inline js, try to make it smaller
$pathNames = [];

// initial struct
$arResult['FOR_JS'] = [
    'DATA'      => [
        'LOCATION'   => [],
        'PATH_NAMES' => [],
    ],
    'CONNECTED' => [
        'LOCATION' => [],
        'GROUP'    => [],
    ],
];

if (is_array($arResult['CONNECTIONS']['LOCATION'])) {
    $arResult['FOR_JS']['DATA']['LOCATION'] = $arResult['CONNECTIONS']['LOCATION'];
}

if (is_array($arResult['FOR_JS']['DATA']['LOCATION']) && !empty($arResult['FOR_JS']['DATA']['LOCATION'])) {
    foreach ($arResult['FOR_JS']['DATA']['LOCATION'] as &$location) {
        $location['VALUE'] = $location['ID'];
        
        $pathIds = [];
        if (is_array($location['PATH'])) {
            $name                = current($location['PATH']);
            $location['DISPLAY'] = $name['NAME'];
            
            foreach ($location['PATH'] as $id => $pathElem) {
                $pathIds[]      = $id;
                $pathNames[$id] = $pathElem['NAME'];
            }
            
            array_shift($pathIds);
            $location['PATH'] = $pathIds;
        }
        //else PATH is supposed to be downloaded on-demand
        
        unset($location['SORT']);
    }
}
unset($location);

$arResult['FOR_JS']['DATA']['PATH_NAMES'] = $pathNames;

// groups
if (is_array($arResult['CONNECTIONS']['GROUP'])) {
    $arResult['FOR_JS']['DATA']['GROUPS'] = $arResult['CONNECTIONS']['GROUP'];
    if (is_array($arResult['FOR_JS']['DATA']['GROUPS']) && !empty($arResult['FOR_JS']['DATA']['GROUPS'])) {
        foreach ($arResult['FOR_JS']['DATA']['GROUPS'] as &$group) {
            $group['DISPLAY'] = $group['NAME'];
            $group['VALUE']   = $group['ID'];
        }
        unset($group);
    }
}

// connected

if (is_array($arResult['CONNECTIONS']['LOCATION']) && !empty($arResult['CONNECTIONS']['LOCATION'])) {
    $arResult['FOR_JS']['CONNECTED']['LOCATION'] = array_keys($arResult['CONNECTIONS']['LOCATION']);
}

if (is_array($arResult['CONNECTIONS']['LOCATION']) && !empty($arResult['CONNECTIONS']['GROUP'])) {
    $arResult['FOR_JS']['CONNECTED']['GROUP'] = array_keys($arResult['CONNECTIONS']['GROUP']);
}