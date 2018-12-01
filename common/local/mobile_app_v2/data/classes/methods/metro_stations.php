<?php
	class metro_stations extends APIServer
	{
		// protected $type='token';

		public function GetMetroTreeInfo($iMetroTreeId = 0)
		{
			CModule::IncludeModule('iblock');

			$arResult = false;

			//получаем список активных конкурсов (разделов) а также информацию по ним
			$oTreeInfo = CIBlockSection::GetList(
			    array(
			    	'SORT' => 'ASC',
					'NAME' => 'ASC',
			    ),
			    array(
			    	'IBLOCK_ID' => CIBlockTools::GetIBlockId('st-metro'),
			    	'ID' => ($iMetroTreeId > 0) ? $iMetroTreeId : '',
			    	'ACTIVE' => 'Y',
			    ),
			    false,
			    array(
			    	'ID',
			    	'NAME',
			    	'UF_METRO_COLOR'
			    )
			);

			while ($arTreeInfo = $oTreeInfo->GetNext())
			{
				$arResult[$arTreeInfo['ID']] = array(
					'NAME' =>  $arTreeInfo['NAME'],
					'COLOR' => $arTreeInfo['UF_METRO_COLOR']
				);
			}

			return $arResult;
		}

		public function get($arInput)
		{
			CModule::IncludeModule('iblock');
			CModule::IncludeModule('catalog');

			$iCityID = -1;

			// проверяем существование ключей и формат
			if (array_key_exists('city_id', $arInput))
			{
				if (is_numeric($arInput['city_id']) && $arInput['city_id'] > 0)
					$iCityID = \city::convGeo2toGeo1($arInput['city_id']);
			}

			if ($iCityID > -1)
			{
				$arTreeInfo = $this->GetMetroTreeInfo();

				$oStationsList = CIBlockElement::GetList(
					array(
						'SORT' => 'ASC',
						'NAME' => 'ASC',
					),
					array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('st-metro'),
						'ACTIVE' => 'Y',
						'PROPERTY_city' => $iCityID,
						'!SECTION_ID' => false
					),
					false,
					false,
					array(
						'ID',
						'NAME',
						'IBLOCK_SECTION_ID'
					)
				);
				while ($arStation = $oStationsList->Fetch())
				{
					$arMetroList[$arStation['IBLOCK_SECTION_ID']][] = array(
						'id' => $arStation['ID'],
						'title' => $arStation['NAME']
					);
				}

				if (!empty($arMetroList))
				{
					foreach ($arMetroList as $iTreeId => $arStations)
					{
						$arResult['metro'][] = array(
							'id' => $iTreeId,
							'title' => ($arTreeInfo[$iTreeId]['NAME']) ?: '',
							'color' => ($arTreeInfo[$iTreeId]['COLOR']) ?: '',
							'stations' => $arStations
						);
					}
				}
				else
					$this->addError('metro_bad_city');
			}
			else
				$this->addError('required_params_missed');

			return($arResult);
		}
	}
?>