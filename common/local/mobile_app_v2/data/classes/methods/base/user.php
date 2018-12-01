<?

use FourPaws\MobileApiOldBundle\User as MyCUser; 
use FourPaws\MobileApiOldBundle\Push\SessionTable;

class user extends \stdClass
{
	private $fields;

	function __construct($id = 0)
	{
		if ($id = intval($id)) {
			$this->fields = \Bitrix\Main\UserTable::getList(array(
				'order' => array('ID' => 'DESC'),
				'filter' => array('=ID' => $id),
				'select' => array('*', 'UF_DISC'),
			))->fetch();
		} else {
			return null;
		}
	}

	private function getCard($cardNumber)
	{
		$arResult = array();

		if ($cardNumber) {
			$arCardInfo = \MyCCard::GetCardBalance($cardNumber);

			if ($arCardInfo['result'] === true) {
				$arResult = array(
					'title' => 'карта клиента',
					'picture' => '',
					'balance' => \MyCCard::GetCardBalanceLocal($cardNumber, $this->fields['ID']),
					'sale_amount' => $arCardInfo['sale_amount'],
					'number' => $this->fields['UF_DISC'],
					'barcode' => ''
				);
			}
		}

		return $arResult;
	}

	public static function getById($id)
	{
		$oUser = new self($id);
		return $oUser->getData();
	}

	public static function getIdByEmail($email)
	{
		if ($email) {
			$arUser = \Bitrix\Main\UserTable::getList(array(
				'filter' => array('=EMAIL' => $email),
				'select' => array('ID'),
			))->fetch();
			return $arUser['ID'];
		} else {
			return null;
		}
	}

	public static function getIdByLogin($login)
	{
		if ($login) {
			$arUser = \Bitrix\Main\UserTable::getList(array(
				'filter' => array('=LOGIN' => $login),
				'select' => array('ID'),
			))->fetch();
			return $arUser['ID'];
		} else {
			return null;
		}
	}

	public function getData()
	{
		$arResult = null;

		$arResult = array(
			'email' => ($this->fields['EMAIL'] ?: ''),
			'firstname' => ($this->fields['NAME'] ?: ''),
			'lastname' => ($this->fields['LAST_NAME'] ?: ''),
			'midname' => ($this->fields['SECOND_NAME'] ?: ''),
			'birthdate' => '',
			'phone' => ($this->fields['PERSONAL_PHONE'] ?: ''),
			'phone1' => ($this->fields['UF_PHONE'] ?: ''),
			'card' => $this->getCard($this->fields['UF_DISC']),
		);

		if ($this->fields['PERSONAL_BIRTHDAY']) {
			try {
				$oDate = new \Bitrix\Main\Type\Date($this->fields['PERSONAL_BIRTHDAY']);
				$arResult['birthdate'] = $oDate->format(API_DATE_FORMAT);
			} catch (Exception $e) {
			}
		}

		return $arResult;
	}

	public function getFuserId()
	{
		if (is_null($this->fields['FUSER_ID'])) {
			\Bitrix\Main\Loader::includeModule('sale');

			$arFuser = \Bitrix\Sale\FuserTable::getList(array(
				'filter' => array('=USER_ID' => ($this->fields['ID'] ?: 0)),
				'select' => array('ID'),
			))->fetch();
			$this->fields['FUSER_ID'] = $arFuser['ID'];
		}

		return $this->fields['FUSER_ID'];
	}

	public static function getIdByFuserId($fuserId)
	{
		$arFuser = \Lapy\Push\SessionTable::getList(array(
			'filter' => array('=FUSER_ID' => $fuserId),
			'select' => array('USER_ID'),
		))->fetch();

		if (!$arFuser) {
			\Bitrix\Main\Loader::includeModule('sale');

			$arFuser = \Bitrix\Sale\FuserTable::getList(array(
				'filter' => array('=ID' => $fuserId),
				'select' => array('USER_ID'),
			))->fetch();
		}

		return ($arFuser ? $arFuser['USER_ID'] : null);
	}

	public function getField($fieldName)
	{
		return $this->fields[$fieldName];
	}

	public function setField($fieldName, $fieldValue)
	{
		$this->fields[$fieldName] = $fieldValue;
	}

	public static function register(array $arFields)
	{
		$oResult = new \Bitrix\Main\Result();

		if (!isset($arFields['LOGIN'])) {
			$oResult->addError(new \error('user_register_no_login'));
		}

		if (!isset($arFields['PASSWORD'])) {
			$oResult->addError(new \error('user_register_no_password'));
		}

		if ($oResult->isSuccess()) {
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth();

			if (!$oBxmodAuth->GetUserByEmail($arFields['LOGIN']) && !$oBxmodAuth->GetUserByPhone($arFields['LOGIN'])) {
				//регистрируем юзера
				$arAuthResult = $oBxmodAuth->Login($arFields['LOGIN'], $arFields['PASSWORD'], false, false, false, true);

				if ($arAuthResult['TYPE'] == 'Register') {
					//добавляем юзера в группу "Не делал заказ в МП"
					MyCUser::setNotMakingOrderInMP($GLOBALS['USER']->GetID());

					$oResult->setData(array(
						'ID' => $GLOBALS['USER']->GetID()
					));
					$GLOBALS['USER']->Logout();
				} else {
					$oResult->addError(new \error('user_register_error_register'));
				}
			} else {
				$oResult->addError(new \error('user_register_busy_login'));
			}
		}

		return $oResult;
	}

	public static function getIdByPhone($phone)
	{
		if ($phone) {
			$phone = \utils::formatPhone($phone);

			$arUser = \Bitrix\Main\UserTable::getList(array(
				'order' => array('ID' => 'DESC'),
				'filter' => array(
					array(
						'LOGIC' => 'OR',
						array('=PERSONAL_PHONE' => $phone),
						array('=LOGIN' => array($phone, '7'.$phone, '8'.$phone)),
					),
					'!EMAIL' => '%@fastorder%'
				),
				'select' => array('ID'),
			))->fetch();
			return $arUser['ID'];
		} else {
			return null;
		}
	}

	public function login($token)
	{
		if(!MyCUser::checkMakingOrderInMPGroups($this->fields['ID']))
		{
			if(MyCUser::checkOrdersFromMP($this->fields['ID']))
			{
				MyCUser::setMakingOrderInMP($this->fields['ID']);
			}
			else
			{
				MyCUser::setNotMakingOrderInMP($this->fields['ID']);
			}
		}

		$arFuser = SessionTable::getList(array(
			'filter' => array('=TOKEN' => $token),
			'select' => array('FUSER_ID'),
		))->fetch();

		$GLOBALS['USER']->Authorize($this->fields['ID']);

		//подтягиваем актуальный номер карты юзера
		$this->fields = \Bitrix\Main\UserTable::getList(array(
			'order' => array('ID' => 'DESC'),
			'filter' => array('=ID' => $this->fields['ID']),
			'select' => array('*', 'UF_DISC'),
		))->fetch();
		//!подтягиваем актуальный номер карты юзера

		CModule::IncludeModule("sale");
		$arFUser = CSaleUser::GetList(array('USER_ID' => $this->fields['ID']));
		// echo($arFUser);

		// \APIServer::createUserSession($this->fields['ID'], null, $token, $arFuser['FUSER_ID']);
		\APIServer::createUserSession($this->fields['ID'], null, $token, ($arFUser['ID'])?:$arFuser['FUSER_ID']);
	}

	public function logout($token)
	{
		$arFuser = SessionTable::getList(array(
			'filter' => array('=TOKEN' => $token),
			'select' => array('FUSER_ID'),
		))->fetch();

		$GLOBALS['USER']->Logout($this->fields['ID']);
		\APIServer::createUserSession(0, null, $token, $arFuser['FUSER_ID']);
	}
}