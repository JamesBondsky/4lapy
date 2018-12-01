<?php
	use Lapy\Push\EventTable;

	class personal_messages extends APIServer
	{
		public function get($arInput)
		{
			// if (isset($arInput['push_token']) && strlen($arInput['push_token']) > 0) {
			// 	$sToken = $arInput['push_token'];
			// } else {
			// 	$this->addError('required_params_missed');
			// }

			$pushParams = $this->getPushParams();

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oEvents = EventTable::getList(array(
					'order' => array('DATE_TIME_EXEC' => 'desc'),
					'filter' => array(
						'=TOKEN' => $pushParams['token'],
						'=SUCCESS_EXEC' => 'Y'
					),
					'select' => array(
						'ID',
						'MESSAGE',
						'DATE_TIME_EXEC',
						'VIEWED'
						)
				));
				while ($arEvent = $oEvents->fetch())
				{
					$arResult['messages'][] = array(
						'id' => $arEvent['ID'],
						'text' => $arEvent['MESSAGE']['TITLE'],
						'date' => $arEvent['DATE_TIME_EXEC']->format("d.m.Y"),
						'read' => ($arEvent['VIEWED'] == 'Y') ? true : false,
						'options' => array(
							'type' => $arEvent['MESSAGE']['TYPE'],
							'id' => $arEvent['MESSAGE']['ID']
							)
						);
				}
			}

			return $arResult;
		}

		public function post($arInput)
		{
			// if (isset($arInput['push_token']) && strlen($arInput['push_token']) > 0) {
			// 	$sToken = $arInput['push_token'];
			// } else {
			// 	$this->addError('required_params_missed');
			// }

			if (isset($arInput['id']) && strlen($arInput['id']) > 0) {
				$id = $arInput['id'];
			} else {
				$this->addError('required_params_missed');
			}

			$pushParams = $this->getPushParams();

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oEvents = EventTable::getList(array(
					'filter' => array(
						'=ID' => $id,
						// '=TOKEN' => $sToken
						'=TOKEN' => $pushParams['token']
						),
					'select' => array('ID')
				));

				if ($arEvent = $oEvents->fetch()) {
					$oResult = EventTable::update($arEvent['ID'], array('VIEWED' => 'Y'));
					$arResult['result'] = ($oResult->isSuccess()) ? true : false;
				}
			}

			return $arResult;
		}

		public function delete($arInput)
		{
			// if (isset($arInput['push_token']) && strlen($arInput['push_token']) > 0) {
			// 	$sToken = $arInput['push_token'];
			// } else {
			// 	$this->addError('required_params_missed');
			// }

			if (isset($arInput['id']) && strlen($arInput['id']) > 0) {
				$id = $arInput['id'];
			} else {
				$this->addError('required_params_missed');
			}

			$pushParams = $this->getPushParams();

			$arResult = false;

			if (!$this->hasErrors())
			{
				$oEvents = EventTable::getList(array(
					'filter' => array(
						'=ID' => $id,
						// '=TOKEN' => $sToken
						'=TOKEN' => $pushParams['token']
						),
					'select' => array('ID')
				));

				if ($arEvent = $oEvents->fetch()) {
					$oResult = EventTable::delete($arEvent['ID']);
					$arResult['result'] = ($oResult->isSuccess()) ? true : false;
				}
			}

			return $arResult;
		}
	}
?>