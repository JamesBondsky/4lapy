<?php
	class filter_list extends APIServer
	{
		protected $type='';

		public function get($arInput)
		{
			\Bitrix\Main\Loader::includeModule('sale');
			\Bitrix\Main\Loader::includeModule('iblock');

			$iSectionID = -1;

			// проверяем существование ключей и формат
			if (array_key_exists('id', $arInput))
			{
				if (is_numeric($arInput['id']) && $arInput['id'] > 0)
					$iSectionID = $arInput['id'];
			}
			if ($iSectionID > -1)
			{
				CBitrixComponent::includeComponentClass("bitrix:catalog.smart.filter");

				$oSmartFilter = new CBitrixCatalogSmartFilter;

				//устанавливаем основные парметры
				$oSmartFilter->IBLOCK_ID = ROOT_CATALOG_ID;
				$oSmartFilter->SECTION_ID = $iSectionID;
				$oSmartFilter->arParams["PRICE_CODE"] = 'base';

				$arResultFilter["PRICES"] = CIBlockPriceTools::GetCatalogPrices($oSmartFilter->IBLOCK_ID, array('base'));

				//получаем фасетные индексы
				$oSmartFilter->facet = new \Bitrix\Iblock\PropertyIndex\Facet($oSmartFilter->IBLOCK_ID);

				//получаем список валют
				$currencyList = \Bitrix\Currency\CurrencyTable::getList(array(
					'select' => array('CURRENCY'),
					'filter' => array('=CURRENCY' => 'RUB')
				));
				if ($currency = $currencyList->fetch())
					$oSmartFilter->convertCurrencyId = $currency['CURRENCY'];

				//получаем свойства для фильтрации
				$arResultFilter["ITEMS"] = $oSmartFilter->getResultItems();

				//получаем значения свойств для фильтрации
				if (!empty($arResultFilter["ITEMS"]))
				{
					//если есть валидный фасетный индекс
					if ($oSmartFilter->facet->isValid())
					{
						$oSmartFilter->facet->setPrices($arResultFilter["PRICES"]);
						$oSmartFilter->facet->setSectionId($oSmartFilter->SECTION_ID);
						$arResultFilter["FACET_FILTER"] = array(
							"ACTIVE_DATE" => "Y",
							"CHECK_PERMISSIONS" => "Y",
							"CATALOG_AVAILABLE" => "Y"
						);

						$res = $oSmartFilter->facet->query($arResultFilter["FACET_FILTER"]);
						CTimeZone::Disable();
						while ($row = $res->fetch())
						{
							$facetId = $row["FACET_ID"];
							if (\Bitrix\Iblock\PropertyIndex\Storage::isPropertyId($facetId))
							{
								$PID = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPropertyId($facetId);
								if ($arResultFilter["ITEMS"][$PID]["PROPERTY_TYPE"] == "N")
								{
									$oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $row["MIN_VALUE_NUM"]);
									$oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $row["MAX_VALUE_NUM"]);
									if ($row["VALUE_FRAC_LEN"] > 0)
										$arResultFilter["ITEMS"][$PID]["DECIMALS"] = $row["VALUE_FRAC_LEN"];
								}
								elseif ($arResultFilter["ITEMS"][$PID]["DISPLAY_TYPE"] == "U")
								{
									$oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], FormatDate("Y-m-d", $row["MIN_VALUE_NUM"]));
									$oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], FormatDate("Y-m-d", $row["MAX_VALUE_NUM"]));
								}
								elseif ($arResultFilter["ITEMS"][$PID]["PROPERTY_TYPE"] == "S")
								{
									$addedKey = $oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $oSmartFilter->facet->lookupDictionaryValue($row["VALUE"]), true);
									if ($addedKey)
									{
										$arResultFilter["ITEMS"][$PID]["VALUES"][$addedKey]["FACET_VALUE"] = $row["VALUE"];
										$arResultFilter["ITEMS"][$PID]["VALUES"][$addedKey]["ELEMENT_COUNT"] = $row["ELEMENT_COUNT"];
									}
								}
								else
								{
									$addedKey = $oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $row["VALUE"], true);
									if ($addedKey)
									{
										$arResultFilter["ITEMS"][$PID]["VALUES"][$addedKey]["FACET_VALUE"] = $row["VALUE"];
										$arResultFilter["ITEMS"][$PID]["VALUES"][$addedKey]["ELEMENT_COUNT"] = $row["ELEMENT_COUNT"];
									}
								}
							}
							else
							{
								$priceId = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPriceId($facetId);

								foreach($arResultFilter["PRICES"] as $NAME => $arPrice)
								{
									if ($arPrice["ID"] == $priceId && isset($arResultFilter["ITEMS"][$NAME]))
									{
										$oSmartFilter->fillItemPrices($arResultFilter["ITEMS"][$NAME], $row);
									}
								}
							}
						}
						CTimeZone::Enable();
					}
					else
					{
						$arElementFilter = array(
							"IBLOCK_ID" => $oSmartFilter->IBLOCK_ID,
							"SUBSECTION" => $oSmartFilter->SECTION_ID,
							"SECTION_SCOPE" => "IBLOCK",
							"ACTIVE_DATE" => "Y",
							"ACTIVE" => "Y",
							"CHECK_PERMISSIONS" => "Y",
							"CATALOG_AVAILABLE" => "Y"
						);

						$arElements = array();

						if (!empty($oSmartFilter->arResult["PROPERTY_ID_LIST"]))
						{
							$rsElements = CIBlockElement::GetPropertyValues($oSmartFilter->IBLOCK_ID, $arElementFilter, false, array('ID' => $oSmartFilter->arResult["PROPERTY_ID_LIST"]));
							while($arElement = $rsElements->Fetch())
								$arElements[$arElement["IBLOCK_ELEMENT_ID"]] = $arElement;
						}
						else
						{
							$rsElements = CIBlockElement::GetList(array('ID' => 'ASC'), $arElementFilter, false, false, array('ID', 'IBLOCK_ID'));
							while($arElement = $rsElements->Fetch())
								$arElements[$arElement["ID"]] = array();
						}

						CTimeZone::Disable();
						$uniqTest = array();
						foreach($arElements as $arElement)
						{
							$propertyValues = $propertyEmptyValuesCombination;
							$uniqStr = '';
							foreach($arResultFilter["ITEMS"] as $PID => $arItem)
							{
								if (is_array($arElement[$PID]))
								{
									foreach($arElement[$PID] as $value)
									{
										$key = $oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $value);
										$propertyValues[$PID][$key] = $arResultFilter["ITEMS"][$PID]["VALUES"][$key]["VALUE"];
										$uniqStr .= '|'.$key.'|'.$propertyValues[$PID][$key];
									}
								}
								elseif ($arElement[$PID] !== false)
								{
									$key = $oSmartFilter->fillItemValues($arResultFilter["ITEMS"][$PID], $arElement[$PID]);
									$propertyValues[$PID][$key] = $arResultFilter["ITEMS"][$PID]["VALUES"][$key]["VALUE"];
									$uniqStr .= '|'.$key.'|'.$propertyValues[$PID][$key];
								}
							}

							$uniqCheck = md5($uniqStr);
							if (isset($uniqTest[$uniqCheck]))
								continue;
							$uniqTest[$uniqCheck] = true;

							$oSmartFilter->ArrayMultiply($arResultFilter["COMBO"], $propertyValues);
						}
						CTimeZone::Enable();

						$arSelect = array("ID", "IBLOCK_ID");
						foreach($arResultFilter["PRICES"] as &$value)
						{
							if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
								continue;
							$arSelect[] = $value["SELECT"];
							$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = 1;
						}
						unset($value);

						$rsElements = CIBlockElement::GetList(array(), $arElementFilter, false, false, $arSelect);
						while($arElement = $rsElements->Fetch())
						{
							foreach($arResultFilter["PRICES"] as $NAME => $arPrice)
								if(isset($arResultFilter["ITEMS"][$NAME]))
									$oSmartFilter->fillItemPrices($arResultFilter["ITEMS"][$NAME], $arElement);
						}
					}

					//сортируем данные
					foreach($arResultFilter["ITEMS"] as $PID => $arItem)
						uasort($arResultFilter["ITEMS"][$PID]["VALUES"], array($oSmartFilter, "_sort"));
				}

				//формируем ответ
				foreach ($arResultFilter['ITEMS'] as $PropId => $arProp)
				{
					// echo "<pre>";print_r($arProp);echo "</pre>"."\r\n";
					$arPropsList = array();

					if ($arProp['CODE'] != 'base')
					{
						foreach ($arProp['VALUES'] as $arPropValue)
						{
							$arPropsList[] = array(
								'id' => $arPropValue['UPPER'],
								'name' => $arPropValue['VALUE']
							);
						}
					}
					else
					{
						$minPrice = $arProp['VALUES']['MIN']['VALUE'];
						$maxPrice = $arProp['VALUES']['MAX']['VALUE'];
					}
					// if($arProp['CODE'] != 'FOR_WHO'):
					if(!in_array($arProp['CODE'], array('FOR_WHO', 'BRAND', 'MAKER', 'TRADE_NAME', 'PRODUCT_FORM', 'FUNCTION', 'TYPE_OF_PARASITE', 'ANIMALS_AGE_BLOSHINKI',   'COLOUR', 'GENDER_OF_ANIMAL', 'CATEGORY', 'SIZE_CLOTHES', 'SEASON_CLOTHES', 'COUNTRY_NAME', 'CODE_COLOUR'))):
						$arResult['filter_list'][] = array(
							'id' =>	$arProp['CODE'],
							'name' => $arProp['NAME'],
							'values' => $arPropsList,
							'min' => ($minPrice)?:'0',
							'max' => ($maxPrice)?:'0'
						);
					endif;
				}
			}
			else
				$this->addError('required_params_missed');

			return($arResult);
		}
	}
?>