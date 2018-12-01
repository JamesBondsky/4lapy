<?php
class order_info extends \APIServer
{
	public function get($arInput)
	{
		$arResult = null;

		if (!(isset($arInput['id']) && $orderId = intval($arInput['id']))) {
			$this->addError('required_params_missed');
		}

		if (!$this->User['user_id']) {
			$this->addError('user_not_authorized');
		}

		if (!$this->hasErrors()) {
			if (\order::isExist($orderId)) {
				$arResult['order'] = (new \order($orderId))->getData();

				$arGoods = array();
				foreach ($arResult['order']['cart_param']['goods'] as $arGood) {
					$arGoods[] = $arGood['goods']['id'];
				}
				if (!empty($arGoods)) {
					\personal_goods::add(array(
						'goods_id_list' => $arGoods
					));
				}
			} elseif ($this->User['UF_DISC'] && $arOrder = \order_mz::isExist($this->User['UF_DISC'], $orderId)) {
				$arOrder = \order_mz::getList(array(
					'filter' => array(
						'=CARD_NUMBER' => $this->User['UF_DISC'],
						'=ID' => $orderId,
					)
				));
				$arOrderFields = reset($arOrder);

				$oOrder = new \order($arOrderFields['ID']);
				$oOrder->setField('USER_ID', $this->User['ID']);
				$oOrder->setField('IS_ROZN', true);
				$oOrder->setFields($arOrderFields);
				$oOrder->setBasket(new \basket_mz(array('order_id' => $arOrderFields['CHEQUE_ID'])));

				$arResult['order'] = $oOrder->getData();
			} else {
				$this->addError('order_not_registered');
			}
		}

		return $arResult;
	}
}