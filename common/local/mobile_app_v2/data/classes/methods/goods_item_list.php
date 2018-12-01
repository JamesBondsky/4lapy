<?php
	class goods_item_list extends APIServer
	{
		protected $type='token';

		public function get($arInput){
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			CModule::IncludeModule("iblock");
			CModule::IncludeModule('catalog');

			// проверяем существование ключей и формат
			if (array_key_exists('id', $arInput))
			{
				if (!empty($arInput['id']))
					$arProductID = $arInput['id'];
			}

			if (!empty($arProductID))
			{
				//получаем список товаров по заданным параметрам
				$oProductsList = CIBlockElement::GetList(
					array(
						'SORT' => 'ASC',
						'NAME' => 'ASC'
					), 
					array(
						'IBLOCK_ID' => ROOT_CATALOG_ID,
						'ID' => $arProductID,
						'ACTIVE' => 'Y'
					),
					false,
					false,
					array(
						'ID',
						'NAME'
					)
				);

				$oGoodsItem = new goods_item;
				$oGoodsItem->User = $this->User;

				while ($arProduct = $oProductsList->Fetch())
				{
					$arResultItem = $oGoodsItem->get(
						array(
							'token' => $this->User['token'],
							'id' => $arProduct['ID']
						)
					);

					$arResult['goods'][] = $arResultItem['goods'];
				}
			}
			else
				$this->res['errors']+=$this->ERROR['required_params_missed'];

			return($arResult);
		}
	}
?>