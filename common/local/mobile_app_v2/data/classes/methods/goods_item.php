<?php
	class goods_item extends APIServer
	{
		protected $type='token';

		public function GetProdItemInfo($iProductID)
		{
			CModule::IncludeModule("iblock");
			CModule::IncludeModule('catalog');

			if ($iProductID)
			{
				$arProdInfo = array();

				//получаем список товаров по заданным параметрам
				$oProductsList = CIBlockElement::GetList(
					array(), 
					array(
						'IBLOCK_ID' => ROOT_CATALOG_ID,
						'ID' => $iProductID,
						'ACTIVE' => 'Y'
					),
					false,
					false,
					array(
						'ID',
						'NAME',
						'PROPERTY_DESCRIPTION_CARD',
						'PROPERTY_IMG',
						// 'PROPERTY_ACTIONS_SHILDS'
					)
				);

				while ($arProduct = $oProductsList->Fetch())
				{
					$arProdDetailInfo[$arProduct['ID']] = $arProduct;
				}

				//получаем цены товаров из списка
				$oPrice = CPrice::GetList(
			        array(),
			        array(
		                "PRODUCT_ID" => array_keys($arProdDetailInfo),
		            ),
		            false,
		            false,
		            array(
		            	'ID',
		            	'PRODUCT_ID',
		            	'CATALOG_GROUP_ID',
		            	'PRICE'
		            )
			    );
				while ($arPrice = $oPrice->Fetch())
				{
				    $arProdPrices[$arPrice['PRODUCT_ID']][$arPrice['CATALOG_GROUP_ID']] = $arPrice['PRICE'];
				}

				$oGoodsList = new goods_list;
				$oGoodsList->User = $this->User;

				//тащим инфу по выбранным товарам
				if ($arProdInfoList = $oGoodsList->GetProdInfo($iProductID))
				{
					foreach ($arProdInfoList as $iProdId => $arProdInfo)
					{
						//формируем результирующий массив
						//формируем поле 'price' в зависимости от наличия скидки
						if ($arProdPrices[$iProdId][2] > 0)
						{
							$arProdInfo['price'] = array(
								'actual' => ($arProdPrices[$iProdId][2]) ? $arProdPrices[$iProdId][2] : '',
								'old' => ($arProdPrices[$iProdId][1]) ? $arProdPrices[$iProdId][1] : ''
							);
						}
						else
						{
							$arProdInfo['price'] = array(
								'actual' => ($arProdPrices[$iProdId][1]) ? $arProdPrices[$iProdId][1] : '',
								'old' => ''
							);
						}

						$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

						//округляем хз как
						$arProdInfo['bonus_user'] = $arProductBonus['bonus_user'];
						$arProdInfo['bonus_all'] = $arProductBonus['bonus_all'];

						//добавляем картинки
						foreach ($arProdDetailInfo[$iProdId]['PROPERTY_IMG_VALUE'] as $iImgId)
						{
							$arProdInfo['picture_list'][] = ($iImgId) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($iImgId) : '';

							if($iImgId)//это будет нашей пасхалкой, мы долго над этим ржали
							{
								$file = CFile::ResizeImageGet($iImgId, array('width'=>'200', 'height'=>'250'), BX_RESIZE_IMAGE_PROPORTIONAL, true);
								$arProdInfo['picture_list_preview'][] = ($iImgId) ? 'https://'.SITE_SERVER_NAME_API.$file['src'] : '';
							}
						}

						//добавляем подробное описание
						$arProdInfo['details_html'] = ($arProdDetailInfo[$iProdId]['PROPERTY_DESCRIPTION_CARD_VALUE']['TEXT']) ? $arProdDetailInfo[$iProdId]['PROPERTY_DESCRIPTION_CARD_VALUE']['TEXT'] : '';
					}

					return $arProdInfo;
				}
			}
		}

		public function get($arInput)
		{
			return $this->post($arInput);
		}

		public function post($arInput)
		{
			$iProductID = -1;

			// проверяем существование ключей и формат
			if (array_key_exists('id', $arInput))
			{
				if (is_numeric($arInput['id']) && $arInput['id'] > 0)
					$iProductID = $arInput['id'];
			}

			if ($iProductID > -1)
			{
				$arResult['goods'] = $this->GetProdItemInfo($iProductID);
			}
			else
				$this->res['errors']+=$this->ERROR['required_params_missed'];

			return($arResult);
		}
	}
?>