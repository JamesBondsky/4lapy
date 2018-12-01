<?php
class shop_goods_available extends APIServer
{
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

		if (!($shopCode = $arInput['shop_id'])) {
			$this->addError('required_params_missed');
		}

		//
		if (!$this->hasErrors()) {
			$arAvailable = \shop::getAvailable($shopCode, $oReqProdCollect);

			if ($arAvailable) {
				$arResult['available_goods'] = (array)$arAvailable['available'];
				$arResult['not_available_goods'] = (array)$arAvailable['not_available'];
				$arResult['shop'] = \shop::getByCode($shopCode);
				$arResult['shop']['availability_status'] = $arAvailable['availability_status'];
				$arResult['availability_date'] = $arAvailable['availability_date'];
			} else {
				$this->addError('shop_available_error');
			}
		}

		return $arResult;
	}
}
