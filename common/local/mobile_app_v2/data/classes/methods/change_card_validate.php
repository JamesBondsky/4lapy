<?
class change_card_validate extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		if (!isset($arInput['new_card_number']) || strlen($arInput['new_card_number']) < 13) {
			$this->addError('required_params_missed');
		} else {
			$new_card_number = filter_var($arInput['new_card_number'], FILTER_SANITIZE_NUMBER_INT);
		}

		$objXml = new CDataXML();
		$client = MyCCard::CreateClient();
		$objAuthenticate = MyCCard::ConnectAdmin($client); 

		$objXml = MyCCard::CardValidate(
			$objXml, 
			$client, 
			$objAuthenticate, 
			array(
				'cardnumber' => $new_card_number,
			)
		);

		$new_card_number_card_id = strip_tags( $objXml->SelectNodes("/cardvalidateresult/cardid") );

		if (!($new_card_number_card_id)){
			$this->addError('card_not_found');
		}

		if ($this->getUserId()) {
			$userId = $this->getUserId();
		} else {
			$this->addError('user_not_authorized');
		}

		if (!$this->hasErrors()) {
			/*Задача: зная номер карты, получить по ней данные из МЛ и вернуть их в МП*/
			/*Или вернуть ошибку*/
			/*Обязательно сделать проверку на совпадение номера телефона(phone)*/

			$oUser = new \user($userId);
			$arUser = $oUser->getData();
			// echo "<pre>";print_r($arUser);echo "</pre>"."\r\n";

			$arUserInfoFromML = MyCCard::SearchContactInfo($new_card_number);

			$userPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $arUser['phone']);
			$cardPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $arUserInfoFromML['PHONE']);
			
			// нужно сравнивать номера без первой цифры // т.к. у пользователя идёт номер 89... а у карты привязан 71...
			$userPhone2 = substr($userPhone, 1, 9);
			$cardPhone = substr($cardPhone, 1, 9);
			////

			if (($userPhone2 == $cardPhone) or empty($cardPhone)) {
				$arResult['profile'] = array(
					'last_name' => $arUserInfoFromML['LAST_NAME'],
					'first_name' => $arUserInfoFromML['FIRST_NAME'],
					'patronymic' => $arUserInfoFromML['SECOND_NAME'],
					'birth_date' => $arUserInfoFromML['BIRTHDAY'],
					'email' => (stristr($arUserInfoFromML['EMAIL'], '@register.phone') === false)?$arUserInfoFromML['EMAIL']:null,
					'phone' => $userPhone
				);
			} else {
				$this->addError('get_card_info_error');
			}
		}

		return $arResult;
	}
}
