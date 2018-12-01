<?

class order_list_v2 extends \APIServer
{
	const ORDER_LIST_LIMIT = 10;

	public function get($arInput)
	{
		$arResult = null;

		if (!$this->User['user_id']) {
			$this->addError('user_not_authorized');
		}

		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('sale');

			$oOrders = \Bitrix\Sale\OrderTable::getList(array(
				'order' => array('ID' => 'DESC'),
				'filter' => array(
					'=USER_ID' => $this->User['user_id'],
					'!=STATUS_ID' => \order::getDisallowStatusId(),
				),
				'select' => array('ID'),
				'limit' => $this::ORDER_LIST_LIMIT
			));

			while ($arOrder = $oOrders->fetch()) {
				$arResult['order_list'][] = (new \order($arOrder['ID']))->getData();
			}

			//если у пользователя имеется номер дисконтной карты, то тянем чеки из манзаны
			if ($this->User['UF_DISC']) {
				$arParams = array(
					'filter' => array('=CARD_NUMBER' => $this->User['UF_DISC'])
				);

				foreach (\order_mz::getList($arParams) as $arOrderFields) {
					$oOrder = new \order($arOrderFields['ID']);
					$oOrder->setField('USER_ID', $this->User['ID']);
					$oOrder->setField('IS_ROZN', true);
					$oOrder->setFields($arOrderFields);
					$oOrder->setBasket(new \basket_mz(array('order_id' => $arOrderFields['CHEQUE_ID'])));

					$arResult['order_list'][] = $oOrder->getData();
				}
			}
		}

		function sortDate($a, $b)
		{
			if (strtotime($a['date']) < strtotime($b['date']))
				return 1;
		}
		usort($arResult['order_list'], 'sortDate');

		return $arResult;
	}
}
