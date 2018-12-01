<?

class logout extends \APIServer
{
	public function get($arInput)
	{
		$oUser = new \user($this->getUserId());
		$oUser->logout($arInput['token']);
		$arResult['feedback_text'] = 'Вы вышли из своей учетной записи';

		return $arResult;
	}
}
