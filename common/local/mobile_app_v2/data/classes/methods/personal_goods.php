<?php
	use Lapy\Goods\MyGoodsTable;

	class personal_goods extends APIServer
	{
		//Получение списка товаров юзера
		public function get($arInput)
		{
			if ($this->getUserId()) {
				$userID = $this->getUserId();
			} else {
				$this->addError('required_params_missed');
			}

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oGoods = MyGoodsTable::getList(array(
					'filter' => array(
						'=USER_ID' => $userID,
						),
					'select' => array(
						'ID',
						'GOODS',
						)
				));
				if($arGoods = $oGoods->fetch())
				{
					$arResult['goods'] = $arGoods['GOODS'];
				}

				if (!empty($arResult['goods'])) 
				{
					$arGoodsDetail = array();
					$oGoodsList = new \goods_list;
					$oGoodsList->User = $this->User;

					//тащим инфу по выбранным товарам
					if ($arProdInfoList = $oGoodsList->GetProdInfo($arResult['goods'])) {
						foreach ($arProdInfoList as $iProdId => $arProdInfo) {
							//получаем количество бонусов по позиции
							$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

							//округляем хз как
							$arProdInfo['bonus_user'] = $arProductBonus['bonus_user'];
							$arProdInfo['bonus_all'] = $arProductBonus['bonus_all'];

							//формируем результирующий массив
							$arGoodsDetail[] = $arProdInfo;
						}
						$arResult['goods'] = $arGoodsDetail;
					} else {
						$this->addError('error_get_prod_info');
					}
				}
			}

			return $arResult;
		}

		//Добавление товара в список
		public function post($arInput)
		{
			$arResult = self::add($arInput);

			return $arResult;
		}

		//Добавление товаров в список
		public function add($arInput)
		{
			if ($this->getUserId()) {
				$userID = $this->getUserId();
			} else {
				$this->addError('required_params_missed');
			}

			if (isset($arInput['goods_id_list']) && !empty($arInput['goods_id_list'])) {
				$arGoodsList = $arInput['goods_id_list'];
			} elseif (isset($arInput['good_id']) && strlen($arInput['good_id']) > 0) {
				$arGoodsList = array(
					$arInput['good_id']
				);
			} else {
				$this->addError('required_params_missed');
			}

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oGoods = MyGoodsTable::getList(array(
					'filter' => array(
						'=USER_ID' => $userID,
						),
					'select' => array(
						'ID',
						'GOODS'
						)
				));

				if ($arGoods = $oGoods->fetch())
				{
					$arGoods['GOODS'] = array_merge($arGoods['GOODS'], $arGoodsList);
					$arGoods['GOODS'] = array_unique($arGoods['GOODS']);
					$oResult = MyGoodsTable::update($arGoods['ID'], array('GOODS' => $arGoods['GOODS']));
					$arResult['result'] = ($oResult->isSuccess()) ? true : false;
				}
				else
				{
					$oResult = MyGoodsTable::add(array(
						'USER_ID' => $userID,
						'GOODS' => $arGoodsList
						));
					$arResult['result'] = ($oResult->isSuccess()) ? true : false;
				}
			}
			return $arResult;
		}

		//Удаление товара из списка
		public function delete($arInput)
		{
			if ($this->getUserId()) {
				$userID = $this->getUserId();
			} else {
				$this->addError('required_params_missed');
			}

			if (isset($arInput['good_id']) && strlen($arInput['good_id']) > 0) {
				$id = $arInput['good_id'];
			} else {
				$this->addError('required_params_missed');
			}

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oGoods = MyGoodsTable::getList(array(
					'filter' => array(
						'=USER_ID' => $userID,
						),
					'select' => array(
						'ID',
						'GOODS'
						)
				));

				if ($arGoods = $oGoods->fetch())
				{
					$arGoods['GOODS'] = array_flip($arGoods['GOODS']);
					unset($arGoods['GOODS'][$id]);
					$arGoods['GOODS'] = array_flip($arGoods['GOODS']);
					$oResult = MyGoodsTable::update($arGoods['ID'], array('GOODS' => $arGoods['GOODS']));
					$arResult['result'] = ($oResult->isSuccess()) ? true : false;
				}
			}

			return $arResult;
		}
	}
?>