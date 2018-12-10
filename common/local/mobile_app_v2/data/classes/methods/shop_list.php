<?



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
		$result = [
		    'shops' => [],
        ];

		if (!$this->hasErrors()) {
			$filter = [];

			if (isset($arInput['city_id']) && intval($arInput['city_id'])) {
                $filter['city_id'] = $arInput['city_id'];
			}

			if(isset($arInput['metro_station']) && !empty($arInput['metro_station'])) {
                $filter['metro'] = $arInput['metro_station'];
			}

            if (isset($arInput['lat']) && isset($arInput['lon']) && $arInput['lat'] && $arInput['lon']) {
                $lat = $arInput['lat'];
                $lon = $arInput['lon'];
            } else {
                // list($lat, $lon) = ($this->getCityCoord(\city::convGeo2toGeo1($cityId) ?: 0) ?: array(0, 0));
            }

            $shops = \shop::getList(['filter' => $filter]);

            if (!$shops) {
                $closestCity = \city_nearest::getNearestId($filter['city_id']);

                if (!$this->hasErrors()) {
                    $shops = \shop::getList(array('filter' => array('city_id' => $closestCity)));
                }
            }
            $result['shops'] = $this->sortShops($shops, $lat, $lon);
		}

		return $result;
	}

	protected function sortShops($shops, $lat = false, $lon = false) {

        if ($lat && $lon) {
            //сортируем по приближенности
            foreach ($shops as &$shop) {
                $shop['dist'] = pow($lat - $shop['lat'], 2) + pow($lon - $shop['lon'], 2);
            }

            unset($shop);
            usort($shops, function ($a, $b) {
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

            foreach ($shops as &$shop) {
                unset($shop['dist']);
            }

        } else {
            usort($shops, function ($a, $b) {
                if ($a['title'] > $b['title']) {
                    return 1;
                } elseif ($a['title'] < $b['title']) {
                    return -1;
                } else {
                    return 0;
                }
            });
        }

	    return $shops;
    }
}
