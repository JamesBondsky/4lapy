<?

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class shop_list extends \APIServer
{
	public function getCityCoord($cityId)
	{
		\Bitrix\Main\Loader::includeModule('iblock');

		$arResult = null;

		$arCity = \CIBlockElement::GetList(
			array(),
			array(
				'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
				'ACTIVE' => 'Y',
				'ID' => $cityId,
			),
			false,
			false,
			array('PROPERTY_gps')
		)->Fetch();

		if ($arCity) {
			list($arResult['lat'], $arResult['lon']) = explode(",", $arCity['PROPERTY_GPS_VALUE']);
		}

		return $arResult;
	}

	public function get($arInput)
	{
		$arResult = array();
		$arResult['shops'] = array();

		if (!$this->hasErrors()) {
			$arFilter = array();

			if (isset($arInput['city_id']) && intval($arInput['city_id'])) {
				$cityId = intval($arInput['city_id']);
				$arFilter['city_id'] = $cityId;
			}

			if(isset($arInput['metro_station']) && !empty($arInput['metro_station'])) {
				$arFilter['metro'] = $arInput['metro_station'];
			}

			if (isset($arInput['lat']) && isset($arInput['lon']) && $arInput['lat'] && $arInput['lon']) {
				$lat = $arInput['lat'];
				$lon = $arInput['lon'];
			} else {
				list($lat, $lon) = ($this->getCityCoord(\city::convGeo2toGeo1($cityId) ?: 0) ?: array(0, 0));
			}

			//
			$oCache = \Bitrix\Main\Data\Cache::createInstance();
			$cacheTime = 60 * 60 * 24 * 7;
			// $cacheId = md5('shop_list|'.join('|', $arFilter));
			$cacheId = md5('shop_list|'.join('|', $arFilter).join('|', $arFilter['metro']));
			$cacheDir = '/shop_list';

			if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
				$arShops = $oCache->getVars();
			} elseif ($oCache->startDataCache()) {
				$arShops = \shop::getList(array('filter' => $arFilter));

				if (!$arShops) {
					$cityNearest = \city_nearest::getNearestId($cityId);

					if (!$this->hasErrors()) {
						$arShops = \shop::getList(array('filter' => array('city_id' => $cityNearest)));
					}
				}

				// вешаем тегированный кеш на ИБ магазинов
				$GLOBALS['CACHE_MANAGER']->StartTagCache($cacheDir);
				$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_'.\CIBlockTools::GetIBlockId('pet-shops'));
				$GLOBALS['CACHE_MANAGER']->EndTagCache();

				$oCache->endDataCache($arShops);
			}

			//
			if ($lat && $lon) {
				//сортируем по приближенности
				foreach ($arShops as &$arShop) {
					$arShop['dist'] = pow($lat - $arShop['lat'], 2) + pow($lon - $arShop['lon'], 2);
				}

				unset($arShop);
				usort($arShops, function ($a, $b) {
					if ($a['dist'] > $b['dist']) {
						return 1;
					} elseif ($a['dist'] < $b['dist']) {
						return -1;
					} elseif ($a['title'] > $b['title']) {
						return 1;
					} elseif ($a['title'] < $b['title']) {
						return -1;
					} else {
						return 0;
					}
				});
			} else {
				usort($arShops, function ($a, $b) {
					if ($a['title'] > $b['title']) {
						return 1;
					} elseif ($a['title'] < $b['title']) {
						return -1;
					} else {
						return 0;
					}
				});
			}

			//
			foreach ($arShops as $arShop) {
				unset($arShop['dist']);
				$arResult['shops'][] = $arShop;
			}
		}

		return $arResult;
	}
}
