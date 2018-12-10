<?

use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\App\Application;

class shop extends \stdClass
{
	public static function getList($arParams = array())
	{
	    $result = null;
        $filter = [];

        if (isset($arParams['filter']['code'])) {
            // $arFilter['PROPERTY_code'] = ($arParams['filter']['code'] ?: '---');
        }

        if (isset($arParams['filter']['city_id'])) {
            $filter['UF_LOCATION'] = $arParams['filter']['city_id'];
        }

        if(isset($arParams['filter']['metro'])
            && is_array($arParams['filter']['metro'])
            && !empty($arParams['filter']['metro'])
        ) {
            $filter['UF_METRO'] = $arParams['filter']['metro'];
        }

        $serviceContainer = Application::getInstance()->getContainer();
        /** @var StoreService $storeService */
        $storeService = $serviceContainer->get('store.service');
        /** @var LocationService $locationService */
        $locationService = $serviceContainer->get('location.service');

        $stores = $storeService->getStores($storeService::TYPE_ALL, $filter)->toArray();
        $metroData = $storeService->getMetroInfo();
        $services = $storeService->getServicesInfo();

        /** @var FourPaws\StoreBundle\Entity\Store $store */
        foreach ($stores as $store) {
            $metro = $metroData[$store->getMetro()];
            $address = $store->getAddress();
            if ($metro) {
                $address = 'м. ' . $metro['UF_NAME'] . ', ' . $address;
            }
            $location = $locationService->findLocationByCode($store->getLocation());
            $result[] = array(
                'city_id' => $location['ID'],
                'id' => $store->getXmlId(),
                'title' => $store->getTitle(),
                'picture' => $store->getSrcImage(),
                'details' => $store->getDescription(),
                'lat' => $store->getLatitude(),
                'lon' => $store->getLongitude(),
                'metro_name' => $metro ? $metro['UF_NAME'] : '',
                'metro_color' => $metro ? $metro['BRANCH']['UF_COLOUR_CODE'] : '',
                'worktime' => $store->getScheduleString(),
                'address' => $address,
                'phone' => $store->getPhone(),
                'phone_ext' => '',
                'url' => '',
                'service' => array_map(function($serviceId) use ($services) {
                    $service = $services[$serviceId];
                    return [
                        'title' => $service['UF_NAME'],
                        'image' => \CFile::getPath($service['UF_FILE']),
                    ];
                }, $store->getServices())
            );
        }
        return $result;


	    /*
		\Bitrix\Main\Loader::includeModule('iblock');

		$arResult = null;
		$arParams['filter']['=IBLOCK_ID'] = \CIBlockTools::GetIBlockId('services-shop');
		$arParams['filter']['=ACTIVE'] = 'Y';
		$arParams['select'] = array_merge($arParams['select'], array('ID', 'NAME'));

		$arFilter = array(
			'IBLOCK_ID' => SHOPS_IBLOCK_ID,
			'ACTIVE' => 'Y',
		);

		if (isset($arParams['filter']['code'])) {
			$arFilter['PROPERTY_code'] = ($arParams['filter']['code'] ?: '---');
		}

		if (isset($arParams['filter']['city_id'])) {
			$arFilter['PROPERTY_city'] = (\city::convGeo2toGeo1($arParams['filter']['city_id']) ?: '0');
		}

		if(isset($arParams['filter']['metro'])
			&& is_array($arParams['filter']['metro'])
			&& !empty($arParams['filter']['metro'])
		) {
			$arFilter['PROPERTY_metro'] = $arParams['filter']['metro'];
		}

		//получаем все активные услуги и информацию по ним
		$arServices = \shop_service::getList(array('select' => array('ID', 'NAME', 'DETAIL_PICTURE')));

		//получение инфо о ветках метро
		$oMetroStations = new \metro_stations;
		$arMetroTreeInfo = $oMetroStations->GetMetroTreeInfo();

		$oShopsList = \CIBlockElement::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			$arFilter,
			false,
			false,
			array(
				'ID',
				'NAME',
				'PROPERTY_address',
				'PROPERTY_add_information',
				'PROPERTY_gps',
				'PROPERTY_code',
				'PROPERTY_city',
				'PROPERTY_city.NAME',
				'PROPERTY_metro',
				'PROPERTY_metro.NAME',
				'PROPERTY_metro.IBLOCK_SECTION_ID',
				'PROPERTY_phone',
				'PROPERTY_work_time',
				'PROPERTY_services',
				'PROPERTY_phone_dob',
			)
		);

		while ($arShop = $oShopsList->Fetch()) {
			//формируем список доступных услуг магазина в нужном формате
			$arCurrentServices = array();

			foreach ($arShop['PROPERTY_SERVICES_VALUE'] as $iServiceId) {
				$arCurrentServices[] = array(
					'image' => $arServices[$iServiceId]['DETAIL_PICTURE'],
					'title' => $arServices[$iServiceId]['NAME']
				);
			}

			if ($arShop['PROPERTY_METRO_VALUE'])
				$sAddress = "м. {$arShop['PROPERTY_METRO_NAME']} , {$arShop['PROPERTY_ADDRESS_VALUE']}";
			else
				$sAddress = $arShop['PROPERTY_ADDRESS_VALUE'];

			//получаем цвет ветки метро
			$sMetroColor = '';
			if ($arShop['PROPERTY_METRO_IBLOCK_SECTION_ID'])
			{
				if (!empty($arMetroTreeInfo[$arShop['PROPERTY_METRO_IBLOCK_SECTION_ID']]))
					$sMetroColor = $arMetroTreeInfo[$arShop['PROPERTY_METRO_IBLOCK_SECTION_ID']]['COLOR'];
			}

			list($lat, $lon) = explode(",", $arShop['PROPERTY_GPS_VALUE']);

			$arResult[] = array(
				'city_id' => \city::convGeo1toGeo2($arShop['PROPERTY_CITY_VALUE']),
				'id' => $arShop['PROPERTY_CODE_VALUE'],
				'title' => $arShop['NAME'],
				'picture' => '',
				'details' => strip_tags($arShop['PROPERTY_ADD_INFORMATION_VALUE']['TEXT'] ?: ''),
				'lat' => ($lat ?: 0),
				'lon' => ($lon ?: 0),
				'metro_name' => ($arShop['PROPERTY_METRO_NAME']) ? $arShop['PROPERTY_METRO_NAME'] : '',
				'metro_color' => $sMetroColor,
				'worktime' => ($arShop['PROPERTY_WORK_TIME_VALUE']) ? $arShop['PROPERTY_WORK_TIME_VALUE'] : '',
				'address' => $sAddress,
				'phone' => ($arShop['PROPERTY_PHONE_VALUE'] ?: ''),
				'phone_ext' => ($arShop['PROPERTY_PHONE_DOB_VALUE'] ?: ''),
				'url' => '',
				'service' => $arCurrentServices
			);
		}

		return $arResult;
	    */
	}

	public static function getByCode($shopCode)
	{
		$arResult = null;

		if ($shopCode) {
			$arResult = self::getList(array(
				'filter' => array('code' => $shopCode)
			));
			$arResult = reset($arResult);
		}

		return $arResult;
	}

	public static function getById($shopId)
	{
		$arResult = null;

		if ($shopId) {
			$arResult = self::getList(array(
				'filter' => array('id' => $shopId)
			));
			$arResult = reset($arResult);
		}

		return $arResult;
	}

	public static function getAvailable($shopCode, \collection $oReqProdCollect, $isConsiderLaterAsAvailable = false)
	{
		// параметр isConsiderLaterAsAvailable указывает рассматривать later_available товары
		// как available или как not_available (по умолчанию - not_available)

		$arResult = null;
		$arProducts = array();

		foreach ($oReqProdCollect as $oItem) {
			$arProducts[$oItem->getField('PRODUCT_ID')] = $oItem->getField('QUANTITY');
		}

		if ($shopCode && !empty($arProducts)) {
			if ($arAvailableWithQty = \Available::ShopsGoodsAvailableWithQty($arProducts, $shopCode)) {
				$oGoodsList = new \goods_list;

				foreach (array('available', 'later_available', 'not_available') as $typeAvailable) {
					if (isset($arAvailableWithQty[$typeAvailable]) && !empty($arAvailableWithQty[$typeAvailable])) {
						foreach ($arAvailableWithQty[$typeAvailable] as $productId => $qty) {
							if ($qty == 0) {
								unset($arAvailableWithQty[$typeAvailable][$productId]);
								continue;
							}

							$arProdInfo = $oGoodsList->GetProdInfo($productId);
							$arProdInfo = reset($arProdInfo);

							if ($typeAvailable == 'not_available') {
								$arResult['not_available'][$productId] = array(
									'goods' => $arProdInfo,
									'qty' => $qty
								);
							} elseif ($typeAvailable == 'available') {
								$arResult['available'][$productId] = array(
									'goods' => $arProdInfo,
									'qty' => $qty
								);
							} elseif ($typeAvailable == 'later_available') {
								if ($isConsiderLaterAsAvailable) {
									if (isset($arResult['available'][$productId])) {
										$arResult['available'][$productId]['qty'] += $qty;
									} else {
										$arResult['available'][$productId] = array(
											'goods' => $arProdInfo,
											'qty' => $qty
										);
									}
								} else {
									if (isset($arResult['not_available'][$productId])) {
										$arResult['not_available'][$productId]['qty'] += $qty;
									} else {
										$arResult['not_available'][$productId] = array(
											'goods' => $arProdInfo,
											'qty' => $qty
										);
									}
								}
							}
						}
					}
				}

				$arResult['available'] = array_values($arResult['available']);
				$arResult['not_available'] = array_values($arResult['not_available']);

				if (empty($arAvailableWithQty['not_available'])) {
					if (!empty($arAvailableWithQty['later_available'])) {
						$arResult['availability_status'] = 'available_later';
					} elseif (!empty($arAvailableWithQty['available'])) {
						$arResult['availability_status'] = 'available';
					} else {
						$arResult['availability_status'] = 'not_available';
					}
				} else {
					if (!empty($arAvailableWithQty['available'])) {
						$arResult['availability_status'] = 'available_part';
					} else {
						$arResult['availability_status'] = 'not_available';
					}
				}

				$arResult['availability_date'] = ($arAvailableWithQty['later_available_date'] ?: '');
			}
		}

		return $arResult;
	}
}
