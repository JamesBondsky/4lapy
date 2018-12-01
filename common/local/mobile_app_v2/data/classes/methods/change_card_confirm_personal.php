<?
class change_card_confirm_personal extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		if ($this->getUserId()) {
			$userId = $this->getUserId();
		} else {
			$this->addError('user_not_authorized');
		}

		if (!isset($arInput['profile']['email']) || !strlen($arInput['profile']['email'])) {
			$this->addError('required_params_missed_email');
		} else {
			$email = filter_var($arInput['profile']['email'], FILTER_VALIDATE_EMAIL);
		}

		if (!$this->hasErrors()) {
			/*Задача: отправить на мыло из запроса код проверки*/
			/*Или вернуть ошибку*/

			$oUser = new \user($userId);
			$ownerOfEmail = $oUser->getIdByEmail($email);

			if (!$ownerOfEmail or ($ownerOfEmail == $userId)) {
				/*Отправим код на мыло юзера*/
				$arData = array(
					'entity' => $email,
					'sender' => 'card_activation'
				);
				$arResult = \captcha::post($arData);
			} else {
				$this->addError('captcha__email_is_used');
			}
		}

		return $arResult;
	}
}
