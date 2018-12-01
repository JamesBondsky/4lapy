<?php
class user_info extends APIServer
{
	public function getMap()
	{
		return array(
			'email' => 'EMAIL',
			'firstname' => 'NAME',
			'lastname' => 'LAST_NAME',
			'midname' => 'SECOND_NAME',
			'birthdate' => 'PERSONAL_BIRTHDAY',
			'phone' => 'PERSONAL_PHONE',
			'phone1' => 'UF_PHONE',
		);
	}

	public function get($arInput)
	{
		$arResult = null;

		if ($this->User['user_id'] > 0) {
			$oUser = new \user($this->User['user_id']);
			$arResult['user'] = $oUser->getData();
		} else {
			$this->addError('user_not_authorized');
		}
		
		// log_($arResult);
		return $arResult;
	}

	public function post($arInput)
	{
		if (!empty($arInput['user'])) {
			if ($this->User['user_id'] > 0) {
				$oUser = new \user($this->User['user_id']);
				$arFields = array();

				foreach ($arInput['user'] as $fieldName => $fieldValue) {
					if ($fieldName == 'birthdate') {
						$arInput['user'][$fieldName] = \ConvertTimeStamp(strtotime($arInput['user']['birthdate']), \CLang::GetDateFormat("SHORT"));
					}	elseif ($fieldName == 'phone' || $fieldName == 'phone1') {
						$fieldValue = preg_replace('/\D/', '', $fieldValue);
					} elseif ($fieldName == 'delivery') {
						continue;
					}

					$map = $this->getMap();
					if ($fieldValue != $oUser->getField($map[$fieldName])) {
						$arFields[$map[$fieldName]] = $fieldValue;
					}
				}

				// при изменении емайла/телефона, также изменяем логин юзера
				if ($oUser->getField('LOGIN') == $oUser->getField('EMAIL')) {
					if (isset($arFields['EMAIL']) && $arFields['EMAIL']) {
						$arFields['LOGIN'] = $arFields['EMAIL'];
					}
				} elseif ($oUser->getField('LOGIN') == $oUser->getField('PERSONAL_PHONE')) {
					if (isset($arFields['PERSONAL_PHONE']) && $arFields['PERSONAL_PHONE']) {
						$arFields['LOGIN'] = $arFields['PERSONAL_PHONE'];
					}
				}

				if ($GLOBALS['USER']->Update($oUser->getField('ID'), $arFields)) {
					$this->User = $this->getUser(array('token' => $arInput['token']));
					$arResult = $this->get($arInput);
				} else {
					$arResult = $GLOBALS['USER']->LAST_ERROR;
				}
			} else {
				$this->addError('user_not_authorized');
			}
		} else {
			$this->addError('required_params_missed');
		}

		return($arResult);
	}
}
