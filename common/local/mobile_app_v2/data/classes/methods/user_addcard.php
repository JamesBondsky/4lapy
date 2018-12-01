<?

class user_addcard extends \APIServer
{
	public function get($arInput)
	{
		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		}

		if ($this->User['UF_DISC']) {
			$this->addError('you_have_card');
		}

		if (!isset($arInput['number']) || !$arInput['number']
			|| !isset($arInput['first_name']) || !$arInput['first_name']
			|| !isset($arInput['last_name']) || !$arInput['last_name']
			|| !isset($arInput['birthdate']) || !$arInput['birthdate']
			|| !isset($arInput['phone']) || !$arInput['phone']
		) {
			$this->addError('required_params_missed');
		} else {
			$cardNumber = $arInput['number'];
			$lastName = $arInput['last_name'];
			$firstName = $arInput['first_name'];
			$birthDate = $arInput['birthdate'];
			$phone = $arInput['phone'];
			$middleName = (isset($arInput['middle_name']) ? $arInput['middle_name'] : '');
			$email = (isset($arInput['email']) ? $arInput['email'] : '');
		}


		if (!$this->hasErrors()) {
			// проверяем, имеется ли на сайте пользователь, с введённой картой
			$oUsers = \Bitrix\Main\UserTable::getList(array(
				'filter' => array('=UF_DISC' => $cardNumber),
				'select' => array('ID'),
			));

			if ($oUsers->getSelectedRowsCount() == 0) {
				// обновляем данные пользователя
				// при этом вызовется событие, которое обновит данные в манзане
				$arFields = array(
					'NAME' => $firstName,
					'LAST_NAME' => $lastName,
					'SECOND_NAME' => $middleName,
					'PERSONAL_PHONE' => $phone,
					'UF_DISC' => $cardNumber,
					'UF_IS_ACTUAL_EMAIL' => 1,
					'UF_IS_ACTUAL_PHONE' => 1
				);

				if ($email) {
					$arFields['EMAIL'] = $email;
				}

				try {
					$arFields['PERSONAL_BIRTHDAY'] = new \Bitrix\Main\Type\Date($birthDate);
				} catch (Exception $e) {
				}

				if ($GLOBALS['USER']->Update($this->getUserId(), $arFields)) {
					$arResult['feedback_text'] = (new \message('user_addcard__update_ok'))->getMessage();
				} else {
					$this->addError('card_add_error');
				}
			} else {
				$this->addError('card_already_added');
			}
		}

		return $arResult;
	}
}
