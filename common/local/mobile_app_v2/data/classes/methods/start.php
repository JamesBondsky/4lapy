<?
class start extends \APIServer
{
	protected $type = '';

	public function get($aInput)
	{
		$arResult = null;

		\Bitrix\Main\Loader::includeModule('sale');

		// получаем токен + сразу резервируем ID корзины
		$token = $this->createUserSession(0, null, '', \CSaleBasket::GetBasketUserID());

		// проверяем успешность создания юзера
		if ($aUser = $this->getUser(array('token' => $token))) {
			// возвращаем токен
			$arResult['token'] = $aUser['token'];
		} else {
			$this->addError('session_not_created');
		}

		return $arResult;
	}
}
