<?php
class user_cart extends APIServer
{
	private function GetBasketAnswer($arBasket)
	{
		$arResult=array();

		foreach ($arBasket['basket']['items'] as $arProd)
		{
			if ($arProd['discount']['value'] > 0)
			{
				$arProdBasketInfo[$arProd['product_id']]['price'] = array(
					'actual' => $arProd['price'],
					'old' => $arProd['price'] + $arProd['discount']['value']
				);
			}
			else
			{
				$arProdBasketInfo[$arProd['product_id']]['price'] = array(
					'actual' => $arProd['price'],
					'old' => ''
				);
			}

			$arProdBasketInfo[$arProd['product_id']]['qty'] = $arProd['quantity'];
		}

		$oGoodsList = new goods_list;
		$oGoodsList->User = $this->User;

		if ($arProdInfoList = $oGoodsList->GetProdInfo(array_keys($arProdBasketInfo)))
		{
			foreach ($arProdInfoList as $iProdId => $arProdInfo)
			{
				$arProdInfo['price'] = $arProdBasketInfo[$iProdId]['price'];

				//получаем количество бонусов по позиции
				$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

				//округляем хз как
				$arProdInfo['bonus_user'] = $arProductBonus['bonus_user'];
				$arProdInfo['bonus_all'] = $arProductBonus['bonus_all'];

				$arResult[] = array(
					'goods' => $arProdInfo,
					'qty' => $arProdBasketInfo[$iProdId]['qty']
				);
			}
		}
		// echo "<pre>"; print_r($arResult); echo "</pre>";
		return $arResult;
	}

	private function GetUserBasket($iFUserId)
	{
		$arResult=array();

		$arBasket = MyCAjax::GetBasket($iFUserId);

		foreach ($arBasket['basket']['items'] as $arProd)
		{
			$arResult[$arProd['product_id']] = array(
				'product_id' => $arProd['product_id'],
				'quantity' => $arProd['quantity']
			);
		}
		// echo "<pre>"; print_r($arResult); echo "</pre>";
		return $arResult;
	}

	//получение корзины пользователя
	public function get($arInput)
	{
		if ($this->User)
		{
			$promocode = (string)$arInput['promocode'];

			if($promocode)
			{
				$promocode_result = MyCAjax::AddCouponAPI($promocode, $this->getFuserId(), $this->getUserId());
			}

			$arBasket = MyCAjax::GetBasket($this->User['basket_id'], $this->User['user_id']);
			if ($arBasket)
			{
				$arResult = array(
					'cart_param' => array(
						'goods' => $this->GetBasketAnswer($arBasket),
						'card' => '',
						'card_used' => '',
						'delivery_type' => '',
						'delivery_place' => '',
						'delivery_date' => '',
						'delivery_time_range' => '',
						'pickup_place' => ''
					),
					'cart_calc' => array(
						'total_price' => array(
							'actual' => $arBasket['basket']['summ'],
							'old' => ''
						),
						'price_details' => array(
							array(
								'id' => 'cart_price',
								'title'	=> 'Товаров в корзине на сумму',
								'value'	=> ($arBasket['basket']['summ'])?:''
							),
							array(
								'id' => 'delivery',
								'title'	=> 'Стоимость доставки',
								'value'	=> ''
							)
						),
						'card_details' => array(
							array(
								'id' => 'bonus_add',
								'title'	=> 'Начислено',
								'value'	=> ''
							),
							array(
								'id' => 'bonus_sub',
								'title'	=> 'Списано',
								'value'	=> ''
							)
						)
					)
				);

				if($promocode)
				{
					$arResult['cart_calc']['promocode_result'] = ($promocode and $promocode_result["result"])?$promocode:'';
				}
				return $arResult;
			}
		}

		return(false);
	}

	//добавление товаров в корзину (принимает id товара и количество)
	public function post($arInput)
	{
		$arResult = null;
		$oReqProdCollect = new \collection;

		foreach ($arInput['goods'] as $arGoods) {
			$oReqProdQty = new \req_product_qty($arGoods);

			if ($oReqProdQty->hasErrors()) {
				$this->addError('required_params_missed');
			} else {
				$oReqProdCollect->addItem(null, $oReqProdQty);
			}
		}

		if (!$this->hasErrors() && !$oReqProdCollect->count()) {
			$this->addError('required_params_missed');
		}

		// проверяем существование ключей и формат
		if (!$this->hasErrors()) {
			if ($this->User) {
				//если товар с таким id существует и активен
				$oGoodsList = new goods_list;
				$oGoodsList->User = $this->User;

				$arGoods = array();

				foreach ($oReqProdCollect as $oProd) {
					if($oGoodsList->CheckProductExistence($oProd->getField('PRODUCT_ID'))) {
						$arGoods[] = array('productId' => $oProd->getField('PRODUCT_ID'), 'quantity' => $oProd->getField('QUANTITY'));
					} else {
						$this->addError('product_not_found');
					}
				}

				if (!empty($arGoods)) {
					$arBasket = MyCAjax::AddProductsToBasket($arGoods, 0, $this->User['basket_id']);

					if ($arBasket) {
						$arResult = $this->get();
					} else {
						$this->addError('basket_add_error');
					}
				} else {
					$this->addError('product_not_found');
				}
			}
		}

		return ($arResult);
	}

	//обновление количества товаров в корзине, 0 - удаление (принимает id товара и количество)
	public function put($arInput)
	{
		$arResult = null;
		$oReqProdCollect = new \collection;

		foreach ($arInput['goods'] as $arGoods) {
			$oReqProdQty = new \req_product_qty($arGoods);

			if ($oReqProdQty->hasErrors()) {
				$this->addError('required_params_missed');
			} else {
				$oReqProdCollect->addItem(null, $oReqProdQty);
			}
		}

		if (!$this->hasErrors() && !$oReqProdCollect->count()) {
			$this->addError('required_params_missed');
		}

		//
		if (!$this->hasErrors()) {
			if ($this->User) {
				//получаем текущее состояние корзины
				$arCurrentBasket = $this->GetUserBasket($this->User['basket_id']);

				foreach ($oReqProdCollect as $oProd) {
					//формируем новое количество товара
					if ($oProd->getField('QUANTITY') == 0) {
						$iNewProductQt = -$arCurrentBasket[$oProd->getField('PRODUCT_ID')]['quantity'];
					} else {
						$iNewProductQt = $oProd->getField('QUANTITY') - $arCurrentBasket[$oProd->getField('PRODUCT_ID')]['quantity'];
					}

					//обновляем количество товара в корзине либо удаляем товар
					$arBasket = MyCAjax::AddProductsToBasket(array(array('productId' => $oProd->getField('PRODUCT_ID'), 'quantity' => $iNewProductQt)), 0, $this->User['basket_id']);
				}

				if ($arBasket) {
					$arResult = $this->get();
				} else {
					$this->addError('basket_update_error');
				}
			}
		}

		return ($arResult);
	}
}
