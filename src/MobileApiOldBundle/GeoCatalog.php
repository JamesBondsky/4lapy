<?

namespace FourPaws\MobileApiOldBundle;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

\CModule::IncludeModule("iblock");
\CModule::IncludeModule("sale");

class GeoCatalog
{
	public static $LAST_ERROR = false;

	// получение населенного пункта
	static function GetCity()
	{
		$city = ($_COOKIE['4lapy_geo']['town_name'])?:false;
		return $city;
	}
	// получение всей инфы по населенному пункту
	static function GetCityFull()
	{
		$city = ($_COOKIE['4lapy_geo'])?:false;
		return $city;
	}

	// получение населенного пункта
	static function GetRegion()
	{
		$sRegion = ($_COOKIE['4lapy_geo']['main_town_name'])?:false;
		return $sRegion;
	}

	// получение магазинов населенного пункта
	static function GetCityShops($city=false)
	{
		$city = ($city)?:self::GetCity();
		if(!$city) return false;

		if(\CModule::IncludeModule("iblock"))
		{
			$arSelect = Array(
				"ID",
				"NAME",
				"PROPERTY_address",
				"PROPERTY_phone",
				"PROPERTY_work_time",
				"PROPERTY_gps",
				"PROPERTY_code",
				"PROPERTY_pickup",
				"PROPERTY_city",
				"PROPERTY_metro",
				);
			$arFilter = Array("IBLOCK_ID"=>CIBlockTools::GetIBlockId('pet-shops'), "ACTIVE"=>"Y", "NAME"=>$city);
			$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arShops[] = array(
					"ID"=>$arFields['ID'],
					"NAME"=>$arFields['NAME'],
					"ADDRESS"=>$arFields['PROPERTY_ADDRESS_VALUE'],
					"PHONE"=>$arFields['PROPERTY_PHONE_VALUE'],
					"WORK_TIME"=>$arFields['PROPERTY_WORK_TIME_VALUE'],
					"GPS"=>$arFields['PROPERTY_GPS_VALUE'],
					"CODE"=>$arFields['PROPERTY_CODE_VALUE'],
					"PICKUP"=>$arFields['PROPERTY_PICKUP_VALUE'],
					"CITY_ID"=>$arFields['PROPERTY_CITY_VALUE'],
					"METRO_ID"=>$arFields['PROPERTY_METRO_VALUE'],
					);
			}
		}

		return $arShops;
	}

	// получение магазинов области
	static function GetRegionShops($sRegion=false)
	{
		$sRegion = ($sRegion)?:self::GetRegion();
		if(!$sRegion) return false;

		if(\CModule::IncludeModule("iblock"))
		{
			$arCity = array();
			//выбираем города региона
			$arFilter = array(
				"IBLOCK_ID" => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
			);

			$arObl = \CIBlockSection::GetList(
				array(),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
					'NAME' => $sRegion
				),
				false,
				array('ID'))->fetch();
			$arFilter['SECTION_ID'] = $arObl["ID"];

			$arSelect = array(
				"NAME",
			);

			$res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arCity[] = $arFields['NAME'];
			}
			//!выбираем города региона

			$arSelect = Array(
				"ID",
				"NAME",
				"PROPERTY_address",
				"PROPERTY_phone",
				"PROPERTY_work_time",
				"PROPERTY_gps",
				"PROPERTY_code",
				"PROPERTY_pickup",
				"PROPERTY_city",
				"PROPERTY_metro",
				);

			$arFilter = Array("IBLOCK_ID"=>CIBlockTools::GetIBlockId('pet-shops'), "ACTIVE"=>"Y", "NAME"=>$arCity);
			$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arShops[] = array(
					"ID"=>$arFields['ID'],
					"NAME"=>$arFields['NAME'],
					"ADDRESS"=>$arFields['PROPERTY_ADDRESS_VALUE'],
					"PHONE"=>$arFields['PROPERTY_PHONE_VALUE'],
					"WORK_TIME"=>$arFields['PROPERTY_WORK_TIME_VALUE'],
					"GPS"=>$arFields['PROPERTY_GPS_VALUE'],
					"CODE"=>$arFields['PROPERTY_CODE_VALUE'],
					"PICKUP"=>$arFields['PROPERTY_PICKUP_VALUE'],
					"CITY_ID"=>$arFields['PROPERTY_CITY_VALUE'],
					"METRO_ID"=>$arFields['PROPERTY_METRO_VALUE'],
					);
			}
		}

		return $arShops;
	}

	// получение магазинов населенного пункта(сервисная)
	private function GetCityShopsServ($city=false)
	{
		$city = ($city)?:self::GetCity();
		if(!$city) return false;

		if(\CModule::IncludeModule("iblock"))
		{
			$arSelect = Array(
				"ID",
				"NAME",
				"PROPERTY_code",
				);
			$arFilter = Array("IBLOCK_ID"=>CIBlockTools::GetIBlockId('pet-shops'), "ACTIVE"=>"Y", "NAME"=>$city);
			$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arShops[] = $arFields['PROPERTY_CODE_VALUE'];
			}
		}

		return $arShops;
	}
	// получение магазинов населенного пункта(сервисная)
	private function GetProvidersServ()
	{
		if(\CModule::IncludeModule("iblock"))
		{
			$arSelect = Array(
				"ID",
				"NAME",
				"XML_ID",
				);
			$arFilter = Array("IBLOCK_ID"=>CIBlockTools::GetIBlockId('providers'), "ACTIVE"=>"Y");
			$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arShops[] = $arFields['XML_ID'];
			}
		}

		return $arShops;
	}
	// получение магазинов региона(сервисная)
	private function GetRegionShopsServ($sRegion=false)
	{
		$sRegion = ($sRegion)?:self::GetRegion();
		if(!$sRegion) return false;

		if(\CModule::IncludeModule("iblock"))
		{
			$arCity = array();
			//выбираем города региона
			$arFilter = array(
				"IBLOCK_ID" => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY)
			);

			$arObl = \CIBlockSection::GetList(
				array(),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
					'NAME' => $sRegion
				),
				false,
				array('ID'))->fetch();
			$arFilter['SECTION_ID'] = $arObl["ID"];

			$arSelect = array(
				"NAME",
			);

			$res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				$arCity[] = $arFields['NAME'];
			}
			//!выбираем города региона

			if ( !empty($arCity) )
			{
				$arSelect = Array(
					"ID",
					"NAME",
					"PROPERTY_code",
					);
				$arFilter = Array("IBLOCK_ID"=>CIBlockTools::GetIBlockId('pet-shops'), "ACTIVE"=>"Y", "NAME"=>$arCity);
				$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				while($arFields = $res->Fetch())
				{
					$arShops[] = $arFields['PROPERTY_CODE_VALUE'];
				}
			}
		}

		return $arShops;
	}

	// получение данных по складам
	static function GetCityShopsStores($arShops = array())
	{
		\CModule::IncludeModule("sale");
		\CModule::IncludeModule("catalog");
		$arShops = (!empty($arShops)) ? $arShops : self::GetCityShopsServ();
		if(empty($arShops)) return false;

		$stores = CCatalogStore::GetList(
			array(),
			array(
				"TITLE"=>$arShops
				),
			false,
			false,
			array()
		);
		while ($arrstores = $stores->Fetch())
		{
			$arStoresId[$arrstores["TITLE"]] = $arrstores["ID"];
		}

		return $arStoresId;
	}
	// получение данных по складам поставщиков
	static function GetProvidersStores()
	{
		\CModule::IncludeModule("sale");
		\CModule::IncludeModule("catalog");
		$arShops = self::GetProvidersServ();
		if(empty($arShops)) return false;

		$stores = CCatalogStore::GetList(
			array(),
			array(
				"TITLE"=>$arShops
				),
			false,
			false,
			array()
		);
		while ($arrstores = $stores->Fetch())
		{
			$arStoresId[$arrstores["TITLE"]] = $arrstores["ID"];
		}

		return $arStoresId;
	}
	static function GetRegionShopsStores($arShops = array())
	{
		$arShops = (!empty($arShops)) ? $arShops : self::GetRegionShopsServ();
		if(empty($arShops)) return false;

		$stores = CCatalogStore::GetList(
			array(),
			array(
				"TITLE"=>$arShops
				),
			false,
			false,
			array()
		);
		while ($arrstores = $stores->Fetch())
		{
			$arStoresId[$arrstores["TITLE"]] = $arrstores["ID"];
		}

		return $arStoresId;
	}

	//еще один суперметод для получения списка id складов города
	static function GetShopsIdByCity($iCityId = false)
	{
		$arResult = false;
		if(\CModule::IncludeModule("iblock"))
		{
			$oShopsList = \CIBlockElement::GetList(
				array(),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('pet-shops'),
					'ACTIVE' => 'Y',
					'PROPERTY_city' => $iCityId
				),
				false,
				false,
				array(
					"ID",
					"NAME",
					"PROPERTY_code"
				)
			);
			while ($arShop = $oShopsList->Fetch())
			{
				$arShopCodes[] = $arShop['PROPERTY_CODE_VALUE'];
			}

			if (!empty($arShopCodes))
			{
				$oStores = CCatalogStore::GetList(
					array(),
					array(
						'TITLE' => $arShopCodes
					),
					false,
					false,
					array(
						'ID',
						'TITLE'
					)
				);
				while ($arStore = $oStores->Fetch())
				{
					$arResult[$arStore["TITLE"]] = $arStore["ID"];
				}
			}
		}
		return $arResult;
	}

	//еще один суперметод для получения названия города и региона по id
	static function GetCityRegionById($iCityId = 0)
	{
		$arResult = false;

		if (\CModule::IncludeModule("iblock") and $iCityId > 0)
		{
			//получаем название города и код раздела (региона)
			$arResult = \CIBlockElement::GetList(
				array(),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
					'ACTIVE' => 'Y',
					'ID' => $iCityId
				),
				false,
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID'
				)
			)->Fetch();

			if ($arResult)
			{
				//получаем инфу о разделе
				$arSectionInfo = \CIBlockSection::GetList(
					array(
						'SORT' => 'ASC'
					),
					array(
						'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
						'ID' => $arResult['IBLOCK_SECTION_ID'],
						'ACTIVE' => 'Y'
					),
					false,
					array(
						'ID',
						'NAME',
						'CODE'
					)
				)->Fetch();

				//дополняем результат
				$arResult['IBLOCK_SECTION_CODE'] = $arSectionInfo['CODE'];
				$arResult['IBLOCK_SECTION_NAME'] = $arSectionInfo['NAME'];
			}
		}

		return $arResult;
	}

	//еще один суперметод для получения названия города и региона по id
	static function GetCityRegionByName($sCityName = '')
	{
		$arResult = false;

		$sCityName = ($sCityName)?:self::GetCity();

		if (\CModule::IncludeModule("iblock") and strlen($sCityName) > 0)
		{
			//получаем название города и код раздела (региона)
			$arResult = \CIBlockElement::GetList(
				array(),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
					'ACTIVE' => 'Y',
					'NAME' => $sCityName
				),
				false,
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID'
				)
			)->Fetch();

			if ($arResult)
			{
				//получаем инфу о разделе
				$arSectionInfo = \CIBlockSection::GetList(
					array(
						'SORT' => 'ASC'
					),
					array(
						'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
						'ID' => $arResult['IBLOCK_SECTION_ID'],
						'ACTIVE' => 'Y'
					),
					false,
					array(
						'ID',
						'NAME',
						'CODE'
					)
				)->Fetch();

				//дополняем результат
				$arResult['IBLOCK_SECTION_CODE'] = $arSectionInfo['CODE'];
				$arResult['IBLOCK_SECTION_NAME'] = $arSectionInfo['NAME'];
			}
		}

		return $arResult;
	}

	// //получение способов доставки
	// static function GetDeliveryList($iLocation, $sLocationZip = '', $iWeight = '', $fPrice, $sCurrency, $sSiteId = null, $arShoppingCart = array())
	// {
	// 	\CModule::IncludeModule("sale");

	// 	$arDeliveries = CSaleDelivery::DoLoadDelivery(
	// 		$iLocation,
	// 		$sLocationZip,
	// 		$iWeight,
	// 		$fPrice,
	// 		$sCurrency,
	// 		$sSiteId,
	// 		$arShoppingCart
	// 	);

	// 	foreach($arDeliveries as $arDelivery)
	// 	{
	// 		$arResult[$arDelivery["ID"]] = array(
	// 			"NAME" => $arDelivery["NAME"],
	// 			"PRICE" => $arDelivery["PRICE"],
	// 			"DESCRIPTION" => $arDelivery["DESCRIPTION"],
	// 		);
	// 	}
	// 	if(!empty($arResult['data']))
	// 		$arResult = $arResult['data'];

	// 	return $arResult;
	// }

	//получение временных интервалов доставки для курьера
	static function GetDeliveryTime($cityId = 0)
	{
		$arResult = false;

		if (!$cityId) {
			$city = self::GetCityFull();
			$cityId = $city['town_id'];
		}

		$city = array(
			'town_id' => $cityId,
			'parents' => array($cityId),
		);

		$dbLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
			'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
			'filter' => array(
				'=ID' => $cityId,
				'!PARENTS.ID' => $cityId,
				'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
				// '=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
			),
			'select' => array('PARENTS_ID' => 'PARENTS.ID'),
			'limit' => 5
		));
		while ($arLocation = $dbLocation->fetch()) {
			$city['parents'][] = $arLocation['PARENTS_ID'];
		}

		if($city):
			\CModule::IncludeModule("iblock");
			$arSelect = Array("ID", "NAME", "CODE", "PROPERTY_LOCATION", "PROPERTY_DAY", "PROPERTY_TIME");
			$arFilter = Array(
				"IBLOCK_ID"=>CIBlockTools::GetIBlockId('delivery_time'),
				"ACTIVE"=>"Y",
				"PROPERTY_LOCATION"=>$city['parents'],
				);
			$res = \CIBlockElement::GetList(Array('CODE'=>'ASC'), $arFilter, false, false, $arSelect);
			while($arFields = $res->Fetch())
			{
				// echo "<pre>";print_r($arFields);echo "</pre>"."\r\n";
				// echo "<pre>";print_r(array('NAME'=>$arFields['NAME'],'CODE'=>$arFields['CODE'],'LOCATION'=>$arFields['PROPERTY_LOCATION_VALUE']));echo "</pre>"."\r\n";
				$arResult[$arFields['CODE']] = array(
					'id' => $arFields['CODE'],
					'label' => $arFields['NAME'],
					'available' => array(
						'time' => $arFields['PROPERTY_TIME_VALUE'],
						'day' => $arFields['PROPERTY_DAY_VALUE'],
					),
				);

				//если количество заказов на сегодняшний вечер превышает лимит, то отменяем сегодняшнюю вечернюю доставку
				if(in_array($arFields['CODE'], array(1)))
				{
					//получаем текущие счетчики
					$limit2week = COption::GetOptionString("sale", "orders_limit_2week");
					$limit2week = unserialize($limit2week);

					if($limit2week[date('Ymd')] >= LIMIT_ORDERS_PER_DAY_MSK)
						$arResult[$arFields['CODE']]['available']['day'] = 1;

					// if($limit2week[date('Ymd', mktime(0, 0, 0, date("m"), date("d")+1, date("Y")))] >= LIMIT_ORDERS_PER_DAY_MSK)
					// 	$arResult[$arFields['CODE']]['available']['day'] = 2;
				}
				//!если количество заказов на сегодняшний вечер превышает лимит, то отменяем сегодняшнюю вечернюю доставку
			}
		endif;
		return $arResult;
	}

	//получение временных интервалов доставки для курьера
	static function GetDeliveryTimeV2($cityId = 0, $fuserID = '')
	{
		$arResult = false;

		if (!$cityId) {
			$city = self::GetCityFull();
			$cityId = $city['town_id'];
		}

		$city = array(
			'town_id' => $cityId,
			'parents' => array($cityId),
		);

		$dbLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
			'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
			'filter' => array(
				'=ID' => $cityId,
				'!PARENTS.ID' => $cityId,
				'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
				// '=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
			),
			'select' => array('PARENTS_ID' => 'PARENTS.ID'),
			'limit' => 5
		));
		while ($arLocation = $dbLocation->fetch()) {
			$city['parents'][] = $arLocation['PARENTS_ID'];
		}

		if($city):
			$arDeliveryDays = array();
			for ($g=0; $g < 20; $g++) { 
				$arDeliveryDays[date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+$g, date("Y")))] = array(
					'date' => date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+$g, date("Y"))),
					'complete' => 1,
					'times' => array(),
					);
			}
			\CModule::IncludeModule("sale");
			//узнаем с какими складами будем иметь дело
			$arCurierStore = GeoCatalog::GetCurierStore($cityId);

			//теперь нам нужно получить состав корзины для дальнейшего расчета
			$dbBasketItems = CSaleBasket::GetList(
				array("NAME" => "ASC", "ID" => "ASC"), 
				array("FUSER_ID" => ($fuserID)?:CSaleBasket::GetBasketUserID(), "ORDER_ID" => "NULL", "CAN_BUY" => 'Y'),
				false,
				false,
				array('PRODUCT_ID', 'PRICE', 'QUANTITY', 'PRODUCT_XML_ID')
				);
			while($arItem = $dbBasketItems->Fetch()){
				$arItemsQ[$arItem['PRODUCT_ID']] = $arItem['QUANTITY'];
			}

			//теперь нам необходимо понять доступность корзины в текущем городе
			$arAvailable = Available::ShopsGoodsAvailableWithQty($arItemsQ, $arCurierStore['sStoreCode']);

			//если все товары смогут доставить
			if(empty($arAvailable['not_available']) and $arAvailable['later_available_date']){
				foreach ($arDeliveryDays as &$arDeliveryDate) {

					if($arDeliveryDate['date'] == $arAvailable['later_available_date']){
						$arDeliveryDate['complete'] = 1;
						break;
					}
					$arDeliveryDate['empty'] = 1;
					foreach ($arAvailable['available'] as $arGood) {
						if ($arGood > 0) {
							$arDeliveryDate['empty'] = 0;
							break;
						}
					}
					$arDeliveryDate['complete'] = 0;
				}
			}
			elseif(!empty($arAvailable['not_available'])){
				foreach ($arDeliveryDays as &$arDeliveryDate) {
					$arDeliveryDate['complete'] = 0;
				}
			}

			\CModule::IncludeModule("iblock");
			$res = \CIBlockElement::GetList(
				Array('SORT'=>'ASC'), 
				Array(
					"IBLOCK_ID"=>CIBlockTools::GetIBlockId('delivery_time'),
					"ACTIVE"=>"Y",
					"PROPERTY_LOCATION"=>$city['parents'],
				), 
				false, 
				false, 
				Array(
					"ID", 
					"NAME", 
					"CODE", 
					"SORT", 
					"PROPERTY_LOCATION", 
					"PROPERTY_DAY", 
					"PROPERTY_TIME"
					)
				);
			while($arFields = $res->Fetch())
			{
				$arResult[$arFields['SORT']] = array(
					'id' => $arFields['CODE'],
					'label' => $arFields['NAME'],
					'available' => array(
						'time' => $arFields['PROPERTY_TIME_VALUE'],
						'day' => $arFields['PROPERTY_DAY_VALUE'],
					),
				);
				//каким-то образом нужно заполнить временные интервалы внутри дней...
			}
			$bFirstDay = true;
			$bSecondDay = false;

			// данные для условия скрытия дней доставок и скрытия времени доставок // вынесены отдельно что бы можно было тестить удобнее
			$g_time = (int) date('H'); // нужно узнать текущее время
			$g_date = strtotime( date('d-m-Y') ); // текущая дата
			$date_l = 1517432400 ;//strtotime( '01-02-2018' ); // левая граница даты - что бы каждый раз не считать
			$date_r = 1517778000 ;//strtotime( '05-02-2018' ); // правая граница даты 
			$arDay_if = array('01.02.2018', '02.02.2018', '03.02.2018', '04.02.2018'/*, '05.02.2018' - 5того будет вечером доставка уже*/); // массив для условия
			$day_if = '05.02.2018';
			////

			foreach ($arDeliveryDays as $keyDD => &$arDeliveryDate) {
				foreach ($arResult as $val => $arData) {
					if($bFirstDay and ($arData['available']['day'] == 1)){
						continue;
					}

					// if ($keyDD == '31.12.2017' and in_array($arData['id'], array(1,5,6))) {
					// 	continue;
					// }

					if ( 
							(  // если сегодня 1.02.2018 и время больше или равно 17.00
								(($date_l == $g_date) && ($g_time >= 17)) 
								or // или если дата между 1.02.2018 и 5.02.2018
								( ($date_l < $g_date) && ($g_date <= $date_r) )
							) 
							&& 
							(
								(	// если в списке дней доставок есть последний день ревизии 5.02.2018 то доставка всегда доступна в этот день с 18-24ч. 
									($keyDD == $day_if)
									&&  
									in_array( $arData['id'], array(0, 6) )
								)
							or
								(  // если день доставки = дням ревизии то убрать все доставки по определённым id доставок 
									in_array($keyDD, $arDay_if )
									&&  
									in_array( $arData['id'], array(0,1,6) ) 
								)
							) 
						)
					{
						continue;
					}

					////

					if ($bSecondDay && $arData['available']['day'] == 1) {
						$date = date_create_from_format('d.m.Y', $arDeliveryDate['date']);
						date_modify($date, '-1 day');
						$date = date_format($date, 'd.m.Y');
					} else {
						$date = $arDeliveryDate['date'];
					}

					$arDeliveryDate['times'][] = array(
						'val' => $arData['id'],
						'label' => $arData['label'],
						'date' => $date,
						'time' => ($bFirstDay or ($bSecondDay and $arData['available']['day'] == 1)) ? $arData['available']['time'] : '23:59'
						);
				}

				if($bFirstDay){
					$bFirstDay = false;
					$bSecondDay = true;
				}
				elseif($bSecondDay){
					$bSecondDay = false;
				}

				//убираем из выдачи 1 и 2 января
				// if ($keyDD == '01.01.2018' or $keyDD == '02.01.2018') {
				// 	unset($arDeliveryDays[$keyDD]);
				// }
				////

			}

		endif;
		
		// почистить дни доставки у которых нет дат доставки
		foreach ($arDeliveryDays as $key => $value) {
			if ( empty($value['times']) )
			{
				unset($arDeliveryDays[$key]);
			}
		}

		// echo "<pre>";print_r($arDeliveryDays);echo "</pre>"."\r\n";	
		return $arDeliveryDays;
	}

	//получение регионального номера телефона
	static function GetRegionPhone($head = false, $cityId = 0)
	{
		$arResult = false;

		\CModule::IncludeModule("iblock");

		if ($cityId > 0) {
			$arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
				'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
				'filter' => array(
					'=ID' => $cityId,
					'!PARENTS.ID' => $cityId,
					'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
					'=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
				),
				'select' => array('PARENTS_ID' => 'PARENTS.ID'),
				'limit' => 1
			))->fetch();

			$city = array(
				'town_id' => $cityId,
				'main_town_id' => ($arLocation['PARENTS_ID'] ?: ''),
			);
		} else {
			$city = self::GetCityFull();
		}
		// echo "<pre>";print_r($city);echo "</pre>"."\r\n";

		if($city):
			$arSelect = Array("ID", "NAME", "PROPERTY_LOCATION", "PROPERTY_DELIVERY_TEXT");
			$arFilter = Array(
				"IBLOCK_ID" 		=> CIBlockTools::GetIBlockId('city_info_geo'),
				"ACTIVE" 			=> "Y",
				"PROPERTY_LOCATION" => array($city['town_id'], $city['main_town_id']),
				);
			$res = \CIBlockElement::GetList(Array('CODE'=>'ASC'), $arFilter, false, false, $arSelect);
			if($arFields = $res->Fetch())
			{
				$arPhone = $arFields['NAME'];
			}
		endif;

		if(!$arPhone):
			if(!$head):
				$arSelect = Array("ID", "NAME", "PROPERTY_LOCATION", "PROPERTY_DELIVERY_TEXT");
				$arFilter = Array(
					"IBLOCK_ID" => CIBlockTools::GetIBlockId('city_info_geo'),
					"ACTIVE" 	=> "Y",
					"CODE" 		=> 'default',
					);
				$res = \CIBlockElement::GetList(Array('CODE'=>'ASC'), $arFilter, false, false, $arSelect);
				if($arFields = $res->Fetch()):
					$arPhone = $arFields['NAME'];
				endif;
			else:
				$arPhone = '';
			endif;
		endif;

		if($arPhone) $arResult = $arPhone;

		return $arResult;
	}

	//получение регионального номера телефона
	static function GetRegionDeliveryText($cityId = 0)
	{
		$arResult = false;

		\CModule::IncludeModule("iblock");

		if ($cityId > 0) {
			$arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
				'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
				'filter' => array(
					'=ID' => $cityId,
					'!PARENTS.ID' => $cityId,
					'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
					'=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
				),
				'select' => array('PARENTS_ID' => 'PARENTS.ID'),
				'limit' => 1
			))->fetch();

			$city = array(
				'town_id' => $cityId,
				'main_town_id' => ($arLocation['PARENTS_ID'] ?: ''),
			);
		} else {
			$city = self::GetCityFull();
		}
		// echo "<pre>";print_r($city);echo "</pre>"."\r\n";

		if($city):
			$arSelect = Array("ID", "NAME", "PROPERTY_LOCATION", "PROPERTY_DELIVERY_TEXT");
			$arFilter = Array(
				"IBLOCK_ID" 		=> CIBlockTools::GetIBlockId('city_info_geo'),
				"ACTIVE" 			=> "Y",
				"PROPERTY_LOCATION" => array($city['town_id'], $city['main_town_id']),
				);
			$res = \CIBlockElement::GetList(Array('CODE'=>'ASC'), $arFilter, false, false, $arSelect);
			if($arFields = $res->Fetch())
			{
				$arDeliveryText = $arFields['PROPERTY_DELIVERY_TEXT_VALUE']['TEXT'];
				// echo "<pre>";print_r($arFields);echo "</pre>"."\r\n";
			}
		endif;

		if(!$arDeliveryText):
			$arSelect = Array("ID", "NAME", "PROPERTY_LOCATION", "PROPERTY_DELIVERY_TEXT");
			$arFilter = Array(
				"IBLOCK_ID" => CIBlockTools::GetIBlockId('city_info_geo'),
				"ACTIVE" 	=> "Y",
				"CODE" 		=> 'default',
				);
			$res = \CIBlockElement::GetList(Array('CODE'=>'ASC'), $arFilter, false, false, $arSelect);
			if($arFields = $res->Fetch())
			{
				$arDeliveryText = $arFields['PROPERTY_DELIVERY_TEXT_VALUE']['TEXT'];
			}
		endif;

		if($arDeliveryText) $arResult = $arDeliveryText;

		return $arResult;
	}

	function GetCurierStore($cityId = 0)
	{
		\CModule::IncludeModule("sale");
		$arResult = array(
			'useLocalStore' => false,
			// 'iStoreId' => false,
			'sStoreCode' => 'dc01',
			);

		$bUseLocalStore = false;

		$arLocalStore = array(
			'Ярославская область' => 'R059',
			'Воронежская область' => 'R169',
			'Тульская область' => 'R186',
			'Ивановская область' => 'R046',
			'Владимирская область' => 'R181',
			'Нижегородская область' => 'R225',
			);
		$arLocalCityStore = array(
			'Обнинск' => 'R079',
			);

		if($cityId)
		{
			$arLocation = CSaleLocation::GetByID($cityId);
			$sRegionName_ = $arLocation['REGION_NAME'];
		}

		$arCity = GeoCatalog::GetCityFull();

		$sRegionName = ($sRegionName_)?:$arCity['main_town_name'];
		$cityId = ($cityId)?:$arCity['town_id'];

		$arDeliveries = CSaleDelivery::DoLoadDelivery($cityId);

		foreach($arDeliveries as $arDelivery)
		{
			if($arDelivery["DESCRIPTION"] == 'courier')
			{
				if(in_array($sRegionName, array_keys($arLocalStore)))
				{
					$useLocalStore = true;

					$arStoresID = self::GetCityShopsStores(array($arLocalStore[$sRegionName]));
					$iStoreId = $arStoresID[$arLocalStore[$sRegionName]];
					$sStoreCode = strtolower($arLocalStore[$sRegionName]);

					$arResult = array(
						'useLocalStore' => $useLocalStore,
						'iStoreId' => $iStoreId,
						'sStoreCode' => $sStoreCode,
						);
				}
				//очередной КОСТЫЛЬ - делаем региональную курьерку для конкретных городов
				if(in_array($arLocation['CITY_NAME'], array_keys($arLocalCityStore)))
				{
					$useLocalStore = true;

					$arStoresID = self::GetCityShopsStores(array($arLocalCityStore[$arLocation['CITY_NAME']]));
					$iStoreId = $arStoresID[$arLocalCityStore[$arLocation['CITY_NAME']]];
					$sStoreCode = strtolower($arLocalCityStore[$arLocation['CITY_NAME']]);

					$arResult = array(
						'useLocalStore' => $useLocalStore,
						'iStoreId' => $iStoreId,
						'sStoreCode' => $sStoreCode,
						);
				}
			}
		}

		return $arResult;
	}

	function GetStreetLocationId($cityId='', $streetName='')
	{
		$streetId = false;
		if ($cityId and $streetName) {
			\CModule::IncludeModule("sale");
			$obLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
				'filter' => array(
					'=NAME.NAME' => $streetName,
					'=PARENTS.ID' => $cityId,
					'=NAME.LANGUAGE_ID' => 'ru',
					'=TYPE.CODE' => array('STREET'),
				),
				'select' => array(
					'ID_LOC' => 'NAME.LOCATION_ID',
					'NAME_LOC' => 'NAME.NAME',
				),
				'limit' => 1
			));

			if ($arLocation = $obLocation->fetch()) {
				$streetId = $arLocation['ID_LOC'];
			}
		}
		return $streetId;
	}
}
?>