<?
class shop_service extends \stdClass
{
	public static function getList($arParams = array())
	{
		\Bitrix\Main\Loader::includeModule('iblock');

		$arResult = null;
		$arParams['filter']['=IBLOCK_ID'] = \CIBlockTools::GetIBlockId('services-shop');
		$arParams['filter']['=ACTIVE'] = 'Y';
		$arParams['select'] = array_merge($arParams['select'], array('ID', 'NAME'));

		$oElements = \Bitrix\Iblock\ElementTable::getList($arParams);

		while ($arElement = $oElements->fetch()) {
			if (isset($arElement['DETAIL_PICTURE']) && $arElement['DETAIL_PICTURE'] > 0) {
				$arElement['DETAIL_PICTURE'] = 'http://'.SITE_SERVER_NAME_API.\CFile::GetPath($arElement['DETAIL_PICTURE']);
			}

			$arResult[$arElement['ID']] = $arElement;
		}

		return $arResult;
	}
}
