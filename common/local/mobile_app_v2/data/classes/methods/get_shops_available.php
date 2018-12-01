<?
class get_shops_available extends \APIServer
{
	public function get($arInput){
		return($this->post($arInput));
	}

	public function post($arInput)
	{
		$arResult = null;
		$arProducts = array();

		foreach ($arInput['goods'] as $arGoods) {
			$oReqProdQty = new \req_product_qty($arGoods);

			if ($oReqProdQty->hasErrors()) {
				$this->addError('required_params_missed');
			} else {
				$arProducts[$oReqProdQty['PRODUCT_ID']] = $oReqProdQty['QUANTITY'];
			}
		}

		if (!$this->hasErrors() && empty($arProducts)) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			$arGoodsIds = array_keys($arProducts);

			$arAvailable = Available::getGoodsStock(
				$arGoodsIds,
				array(
					'type'=>'long'
				)
			);

			$arShopsCode = $arAvailable[$arGoodsIds[0]]['STORES']['SHOPS'];

			foreach ($arShopsCode as $idP => $stock) {
				if($stock < $arProducts[$arGoodsIds[0]]) unset($arShopsCode[$idP]);
			}

			$arResult['shops'] = \shop::getList(array('filter' => array('code' => array_keys($arShopsCode))));

			if(empty($arResult['shops'])){
				$this->addError('shop_available_error');
				return null;
			}else{
				foreach ($arResult['shops'] as $kShop => $shop) {
					$arResult['shops'][$kShop]['availability_status'] = 'available';
				}
			}
		}

		return $arResult;
	}
}
