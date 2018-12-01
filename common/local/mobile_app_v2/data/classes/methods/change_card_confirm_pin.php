<?
class change_card_confirm_pin extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		if ($this->getUserId()) {
			$userId = $this->getUserId();
		} else {
			$this->addError('user_not_authorized');
		}

		if (!isset($arInput['profile']['email']) || !strlen($arInput['profile']['email'])
			|| !isset($arInput['captcha_id']) || !strlen($arInput['captcha_id'])
			|| !isset($arInput['captcha_value']) || !strlen($arInput['captcha_value'])
		) {
			$this->addError('required_params_missed');
		} else {
			$entity = filter_var($arInput['profile']['email'], FILTER_VALIDATE_EMAIL);
			$captchaSid = $arInput['captcha_id'];
			$captchaCode = $arInput['captcha_value'];
			$cardNumber = $arInput['profile']['new_card_number'];
		}

		if (!$this->hasErrors()) {
			/*Задача: проверить капчу, и если все гут - апдейтить юзера в БД*/
			/*Или вернуть ошибку*/
			$arData = array(
				'entity' => $entity,
				'captcha_id' => $captchaSid,
				'captcha_value' => $captchaCode,
			);
			$arResult = $verifyResult = \verify::post($arData);

			if($verifyResult['captcha_id']){
				/*Если капча проверена корректно - апдейтим юзера*/

				// проверяем, имеется ли на сайте пользователь, с введённой картой
				$oUsers = \Bitrix\Main\UserTable::getList(array(
					'filter' => array('=UF_DISC' => $cardNumber),
					'select' => array('ID'),
				));

				if ($oUsers->getSelectedRowsCount() == 0) {
					// обновляем данные пользователя
					// при этом вызовется событие, которое обновит данные в манзане
					$arFields = array(
						'NAME' => $arInput['profile']['first_name'],
						'LAST_NAME' => $arInput['profile']['last_name'],
						'SECOND_NAME' => $arInput['profile']['patronymic'],
						'UF_DISC' => $cardNumber,
						'UF_IS_ACTUAL_EMAIL' => 1,
					);

					if ($arInput['profile']['email']) {
						$arFields['EMAIL'] = $arInput['profile']['email'];
					}

					try {
						$arFields['PERSONAL_BIRTHDAY'] = new \Bitrix\Main\Type\Date($arInput['profile']['birth_date']);
					} catch (Exception $e) {
					}

					/*ОТ ТУТ НАДА ДЕРНУТЬ МЕТОД ОТ СЕРЕЖИ ДЛЯ ЗАМЕНЫ КАРТЫ, ПОТОМ АПДЕЙТИМ ЮЗЕРА*/

			$oUser = new \user($userId);
			$arUser = $oUser->getData();
			$oldCard = $arUser['card']['number'];

	$objXml = new CDataXML();
	$client = MyCCard::CreateClient();
	$objAuthenticate = MyCCard::ConnectAdmin($client); 

	// заменяем карты 
	$old_card_id = false;
	$new_card_id = false;

	$objXml = MyCCard::CardValidate(
		$objXml, 
		$client, 
		$objAuthenticate, 
		array(
			'cardnumber' => $oldCard,
		)
	);

	$old_card_id = strip_tags( $objXml->SelectNodes("/cardvalidateresult/cardid") );
	////
	
	$objXml = MyCCard::CardValidate(
		$objXml, 
		$client, 
		$objAuthenticate, 
		array(
			'cardnumber' => $cardNumber,
		)
	);

	$new_card_id = strip_tags( $objXml->SelectNodes("/cardvalidateresult/cardid") );

	//заменить карты
	if ( !empty($old_card_id) && !empty($new_card_id) )
	{
		$objXml = MyCCard::ContactCardUpdate( // !!! это надо ещё протестить
			$objXml, 
			$client, 
			$objAuthenticate, 
			array(
				'card_from' => $old_card_id,
				'card_to' => $new_card_id,
			)
		);

		if ($objXml->SelectNodes("/result")->content == "Замена карты произошла успешно!") // если заменило
		{
			$arResult['feedback_text'] = (new \message('user_addcard__update_ok'))->getMessage();
		} else {
			$this->addError('card_add_error');
		}

	} else {
		$this->addError('card_add_error');
	}

					/*ОТ ТУТ НАДА ДЕРНУТЬ МЕТОД ОТ СЕРЕЖИ ДЛЯ ЗАМЕНЫ КАРТЫ, ПОТОМ АПДЕЙТИМ ЮЗЕРА*/

					if ($GLOBALS['USER']->Update($this->getUserId(), $arFields)) {
						$arResult['feedback_text'] = (new \message('user_addcard__update_ok'))->getMessage();
					} else {
						$this->addError('card_add_error');
					}
				} else {
					$this->addError('card_already_added');
				}

			}
		}

		return $arResult;
	}
}
