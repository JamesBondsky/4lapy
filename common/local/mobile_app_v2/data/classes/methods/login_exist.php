<?

class login_exist extends \APIServer
{
	public function get($arInput)
	{
		$arResult = null;

		$login = (isset($arInput['login']) ? $arInput['login'] : null);

		if (!$login) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth();

			// проверяем существование юзера в битриксе
			if ($oBxmodAuth->GetUserByEmail($login) || $oBxmodAuth->GetUserByPhone($login)) {
				$arResult = array(
					'exist' => true,
				);
			} else {
				$arResult = array(
					'exist' => false,
					'feedback_text' => 'Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона',
				);
			}
		}

		return $arResult;
	}
}
