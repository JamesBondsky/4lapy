<?php
	class goods_search_barcode extends APIServer
	{
		protected $type='token';

		public function get($arInput)
		{
			CModule::IncludeModule('iblock');
			CModule::IncludeModule('search');

			// проверяем существование ключей и формат
			if (array_key_exists('barcode', $arInput))
			{
				if (strlen($arInput['barcode']) > 0)
					$sRequest = $arInput['barcode'];
			}
			
			if ($sRequest)
			{
				//выполняем поиск по фразе только в каталоге товаров и выводим 50 результатов
				$oSearch = new CSearch;
				
				$oSearch->SetOptions(array(
					'ERROR_ON_EMPTY_STEM' => false,
					'NO_WORD_LOGIC' => false,
				));
				
				$arFilter = array(
					'SITE_ID' => (\Bitrix\Main\Application::getInstance()->getContext()->getSite() ?: 's1'),
					'QUERY' => $sRequest,
					// 'MODULE_ID' => 'iblock',
					// 'PARAM2' => array(ROOT_CATALOG_ID)
				);
				$aSort = array(
					'CUSTOM_RANK' => 'DESC',
					'TITLE_RANK' => 'DESC',
					'RANK' => 'DESC',
					'DATE_CHANGE' => 'DESC',
				);
				$exFILTER = array(
					'=MODULE_ID' => 'iblock',
					'PARAM1' => 'content',
					'PARAM2' => array(ROOT_CATALOG_ID),
					'STEMMING' => false
				);
				
				$oSearch->Search($arFilter, $aSort, $exFILTER, false, true);
				
				$oSearch->NavStart(1, false);
				
				
				if ($oSearch->errorno == 0)
				{
				    while ($arSearchItem = $oSearch->GetNext())
				    {
						$iFindProdId = $arSearchItem['ITEM_ID'];
				    }
				}
				
				if ($iFindProdId)
				{
					//получаем список ID товаров по заданным параметрам
					$oProductsList = CIBlockElement::GetList(
						array(
							'SORT' => 'ASC',
							'NAME' => 'ASC'
						), 
						array(
							'IBLOCK_ID' => ROOT_CATALOG_ID,
							'ACTIVE' => 'Y',
							'ID' => $iFindProdId
						),
						false,
						false,
						array(
							'ID',
							'NAME'
						)
					);

					$oGoodsItem = new goods_item;

					while ($arProduct = $oProductsList->GetNext())
					{
						$arResult = $oGoodsItem->get(array(
							'token' => $arInput['token'],
							'id' => $arProduct['ID']
						));
					}
				}
				// else
				// 	$this->res['errors']+=$this->ERROR['bad_search'];
			}
			else
				$this->res['errors']+=$this->ERROR['required_params_missed'];

			return($arResult);
		}
	}
?>