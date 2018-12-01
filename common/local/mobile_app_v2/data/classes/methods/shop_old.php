<?php
	class shop extends APIServer
	{
		// protected $type='token';

		public function get($arInput)
		{
			CModule::IncludeModule("iblock");
			CModule::IncludeModule('catalog');

			$iShopID = -1;
			$arResult = array();

			// проверяем существование ключей и формат
			if (array_key_exists('shop_id', $arInput))
			{
				if (is_numeric($arInput['shop_id']) && $arInput['shop_id'] > 0)
					$iShopID = $arInput['shop_id'];
			}

			if ($iShopID > -1)
			{
				$oShopsList = CIBlockElement::GetList(
					array(
						'SORT' => 'ASC',
						'NAME' => 'ASC'
					), 
					array(
						'ID' => $iShopID,
						'IBLOCK_ID' => SHOPS_IBLOCK_ID,
						'ACTIVE' => 'Y',
					),
					false,
					false,
					array(
						'ID',
						'NAME',
						'PROPERTY_address',
						'PROPERTY_add_information',
						'PROPERTY_gps',
						'PROPERTY_address',
						'PROPERTY_city',
						'PROPERTY_city.NAME',
						'PROPERTY_metro',
						'PROPERTY_metro.NAME',
						'PROPERTY_phone'
					)
				);

				while ($arShop = $oShopsList->Fetch())
				{
					$arCoords = explode(",", $arShop['PROPERTY_GPS_VALUE']);

					// $sAddress = ".г {$arShop['PROPERTY_CITY_NAME']}";

					if ($arShop['PROPERTY_METRO_VALUE'])
						$sAddress = "м. {$arShop['PROPERTY_METRO_NAME']} , {$arShop['PROPERTY_ADDRESS_VALUE']}";
					else
						$sAddress = $arShop['PROPERTY_ADDRESS_VALUE'];

					$arResult = array(
						'title' => $arShop['NAME'],
						'picture' => '',
						'details' => ($arShop['PROPERTY_ADD_INFORMATION_VALUE']['TEXT']) ? $arShop['PROPERTY_ADD_INFORMATION_VALUE']['TEXT'] : "",
						'lat' => ($arCoords[0]) ? $arCoords[0] : 0,
						'lon' => ($arCoords[1]) ? $arCoords[1] : 0,
						'address' => $sAddress,
						'phone' => $arShop['PROPERTY_PHONE_VALUE']
					);
				}
			}

			return($arResult);
		}
	}
?>