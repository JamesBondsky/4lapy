<?

class city_list extends \APIServer
{

	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$oCache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheTime = 60 * 60 * 24 * 7;
		$cacheId = md5('city_list_04_18');
		$cacheDir = '/city_list_04_18';

		if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
			$arResult = $oCache->getVars();
		} elseif ($oCache->startDataCache()) {
			$arResult = \city::getListDefaultSite();
			$oCache->endDataCache($arResult);
		}

		return $arResult;
	}
}
