<?
class shops_list_available extends \APIServer
{
	public function get($arInput){
		return($this->post($arInput));
	}
	
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

		if (!($cityId = intval($arInput['city_id']))) {
			$this->addError('required_params_missed');
		}

		//
		if (!$this->hasErrors()) {
			$arResult['shops'] = \shop::getList(array('filter' => array('city_id' => $cityId)));

			if (!$arResult['shops']) {
				$cityNearest = \city_nearest::getNearestId($cityId);

				if (!$this->hasErrors()) {
					$arResult['shops'] = \shop::getList(array('filter' => array('city_id' => $cityNearest)));
				}
			}

			$bIsAvailable = false;
			if (!$this->hasErrors() && $arResult['shops']) {
				foreach ($arResult['shops'] as &$arShop) {
					$arAvailable = \shop::getAvailable($arShop['id'], $oReqProdCollect);
					if ($arAvailable) {
						if ($arAvailable['availability_status'] == 'available' || $arAvailable['availability_status'] == 'available_part') {
							$bIsAvailable = true;
						}
						$arShop['availability_status'] = $arAvailable['availability_status'];
					} else {
						$this->addError('shop_available_error');
						return null;
					}
				}

				unset($arShop);
			}

			// if ($bIsAvailable) {
			// 	// $arResult['shops'] = array_filter($arResult['shops'], function($arShop) {
			// 	// 	return ($arShop['availability_status'] != 'available_later' && $arShop['availability_status'] != 'not_available');
			// 	// });
			// 	$arResult['shops'] = array_values($arResult['shops']);
			// }
		}

		return $arResult;
	}
}
