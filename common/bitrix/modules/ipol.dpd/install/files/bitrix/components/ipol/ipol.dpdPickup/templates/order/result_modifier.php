<?php
if ($arResult['TERMINALS']) {
	$arResult['TERMINAL_TYPES'] = array();
	foreach ($arResult['TERMINALS'] as $key => $value) {
		if (!empty($value['PARCEL_SHOP_TYPE'])) {
			$arResult['TERMINAL_TYPES'][] = $value['PARCEL_SHOP_TYPE'];
		}
	}

	$arResult['TERMINAL_TYPES'] = array_unique($arResult['TERMINAL_TYPES']);
} else {
	$arResult['TERMINAL_TYPES'] = array();
}