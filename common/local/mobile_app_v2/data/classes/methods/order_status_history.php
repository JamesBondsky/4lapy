<?php
class order_status_history extends APIServer
{
	protected $arStatuses = array();

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
			// $this->arStatuses[$arOrdersStat['ID']] = $arOrdersStat['NAME'];
			$this->arStatuses[$arOrdersStat['ID']] = array('code'=>$arOrdersStat['ID'], 'title'=>$arOrdersStat['NAME']);
		}
	}

	public function get($arInput)
	{
		$arResult = array();

		if (!($orderId = intval($arInput['id']))) {
			$this->addError('order_not_registered');
		}

		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('sale');

			if ($arOrder = \Bitrix\Sale\OrderTable::getRowById($orderId)) {
				// получаем текстовые значения статусов заказов
				$this->GetOrderStatuses();

				$arResult['status_history'][] = array(
					'status' => $this->arStatuses['N'],
					'date' => $arOrder['DATE_INSERT']->format(API_DATE_FORMAT),
					'time' => $arOrder['DATE_INSERT']->format(API_TIME_FORMAT)
				);

				$oOrderChanges = \CSaleOrderChange::GetList(
					array('DATE_CREATE' => 'ASC'),
					array(
						'ORDER_ID' => $orderId,
						'TYPE' => 'ORDER_STATUS_CHANGED'
					),
					false,
					false,
					array('DATE_CREATE', 'DATA')
				);

				while($arOrderChange = $oOrderChanges->Fetch()) {
					$arData = unserialize($arOrderChange['DATA']);
					$oDateCreate = new \Bitrix\Main\Type\DateTime($arOrderChange['DATE_CREATE']);

					//формируем ответ
					$arResult['status_history'][] = array(
						'status' => $this->arStatuses[$arData['STATUS_ID']],
						'date' => $oDateCreate->format(API_DATE_FORMAT),
						'time' => $oDateCreate->format(API_TIME_FORMAT)
					);
				}
			} else {
				$this->addError('order_not_registered');
			}
		}

		return $arResult;
	}
}
