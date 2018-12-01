<?php
class street extends \stdClass
{
	const PAGE_SIZE = 100;

	public static function getList($arParams = array())
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = array(
			'page' => $arParams['page']
		);
		$arFilter = array();

		if (isset($arParams['filter'])) {
			$arFilter = $arParams['filter'];
		}

		$arFilter['=TYPE_ID'] = 7;
		$arFilter['=NAME.LANGUAGE_ID'] = 'ru';

		$oStreets = \Bitrix\Sale\Location\LocationTable::getList(array(
			'filter' => $arFilter,
			'order' => 'NAME.NAME'
		));

		$arResult['total_items'] = $oStreets->getSelectedRowsCount();
		$arResult['total_pages'] = ceil($oStreets->getSelectedRowsCount() / self::PAGE_SIZE);

		$oStreets = \Bitrix\Sale\Location\LocationTable::getList(array(
			'filter' => $arFilter,
			'select' => array(
				'ID',
				'LNAME' => 'NAME.NAME'
			),
			'limit' => self::PAGE_SIZE,
			'offset' => self::PAGE_SIZE * ($arParams['page'] - 1),
			'order' => 'NAME.NAME'
		));

		while ($arStreet = $oStreets->Fetch()) {
			$arResult['street_list'][] = array(
				'id' => $arStreet['ID'],
				'title' => $arStreet['LNAME']
			);
		}

		return $arResult;
	}

}
