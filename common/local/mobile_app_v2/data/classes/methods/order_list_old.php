<?php
	class order_list_old extends APIServer
	{
		protected $type='token';
		protected $arStatuses = array();
		protected $arOrdersId = array();
		protected $arResultOrder = array();

		private function GetOrderStatuses()
		{
			$oOrdersStat = CSaleStatus::GetList(
   				array(), 
   				array('LID' => 'ru'),
 	   			false,
 	   			false,
 	   			array('ID', 'NAME')
   			);
   			while ($arOrdersStat = $oOrdersStat->Fetch())
			{
				$this->arStatuses[$arOrdersStat['ID']] = $arOrdersStat['NAME'];
			}
		}

		private function GetOrderConsist($arOrdersId)
		{
			$oGoodsList = new goods_list;
			
			$oOrderList = CSaleBasket::GetList(
	            array("SORT" => "ASC"),
	            array(
	                "ORDER_ID" => $arOrdersId,
	            ),
	            false,
	            false,
	            array(
	            	'ID',
	            	'PRODUCT_ID',
	            	'ORDER_ID',
	            	'PRICE',
	            	'QUANTITY',
	            	'DISCOUNT_PRICE',
	            	'DISCOUNT_PRICE',
	            )
	        );

	        while ($arOrderList = $oOrderList->Fetch())
	        {
	        	//получаем основную инфу по товарам
	            $arProdInfo = $oGoodsList->GetProdInfo($arOrderList['PRODUCT_ID']);
	            $arProdInfo = reset($arProdInfo);

	            //формируем стоимость позиции
				if ($arOrderList['DISCOUNT_PRICE'] > 0)
				{
					$arProdInfo['price'] = array(
						'actual' => $arOrderList['PRICE'],
						'old' => $arOrderList['DISCOUNT_PRICE'] + $arOrderList['PRICE']
					);
				}
				else
				{
					$arProdInfo['price'] = array(
						'actual' => $arOrderList['PRICE'],
						'old' => ''
					);
				}

				//получаем количество бонусов по позиции
				$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price']);

				//округляем хз как
				$arProdInfo['bonus_user'] = ceil($arProductBonus['bonus_user']);
				$arProdInfo['bonus_all'] = ceil($arProductBonus['bonus_all']);

				$this->arResultOrder[$arOrderList['ORDER_ID']]['basket'][] = array(
					'goods' => $arProdInfo,
					'qty' => (int) $arOrderList['QUANTITY']
				);
	        }
		}

		public function get($arInput)
		{
			CModule::IncludeModule('sale');

			$arResult = array();

			$this->GetOrderStatuses();

   			$oOrder = CSaleOrder::GetList(
   				array(), 
   				array(
	   				"USER_ID" => $this->User['ID']
	   				// 'ID' => $arOrders = array('49136', '49264')
 	   			),
 	   			false,
 	   			false,
 	   			array(
 	   				'ID',
 	   				'STATUS_ID',
 	   				'PRICE_DELIVERY',
 	   				'PRICE',
 	   				'DISCOUNT_VALUE',
 	   				'SUM_PAID',
 	   				'USER_ID',
 	   				'DATE_INSERT',
 	   				'DATE_INSERT'
 	   			)
   			);
   			while ($arOrder = $oOrder->Fetch())
			{
				$this->arOrdersId[] = $arOrder['ID'];

				$this->arResultOrder[$arOrder['ID']] = array(
					'number' => $arOrder['ID'],
					'date' => ($arOrder['DATE_INSERT']) ? date(API_DATE_FORMAT, strtotime($arOrder['DATE_INSERT'])) : '',
					'time' => ($arOrder['DATE_INSERT']) ? date(API_TIME_FORMAT, strtotime($arOrder['DATE_INSERT'])) : '',
					'status' => $this->arStatuses[$arOrder['STATUS_ID']],
					'total_price' => array(
						'actual' => $arOrder['PRICE'],
						'old' => ''
					),
					'price_details' => array(
						"Стоимость товаров: ".(($arOrder['PRICE_DELIVERY'] > 0) ? $arOrder['PRICE'] - $arOrder['PRICE_DELIVERY'] : $arOrder['PRICE'])." Р",
						"Доставка: {$arOrder['PRICE_DELIVERY']} Р"
					),
					'card' => ($arOrder['SUM_PAID'] > 0) ? $this->User['UF_DISC'] : '',
					'card_details' => array(
						"Списано {$arOrder['SUM_PAID']}"
					),

				);
			}

			$this->GetOrderConsist($this->arOrdersId);
			$arResult['order_list'] = array_values($this->arResultOrder);
			// echo "<pre>"; print_r($arResult); echo "</pre>";

			return($arResult);
		}
	}
?>