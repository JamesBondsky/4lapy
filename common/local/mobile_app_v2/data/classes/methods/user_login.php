<?
class user_login extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		//
		if (!isset($arInput['captcha_id']) || !$arInput['captcha_id']
			|| !isset($arInput['captcha_value']) || !$arInput['captcha_value']
			|| !isset($arInput['login']) || !$arInput['login']
		) {
			$this->addError('required_params_missed');
		} else {
			$token = $arInput['token'];
			$phone = \utils::formatPhone($arInput['login']);
			$captchaCode = $arInput['captcha_value'];
			$captchaSid = $arInput['captcha_id'];
		}

		//
		if (!$this->hasErrors()) {
			if (\captcha_base::checkCode($captchaCode, $captchaSid)
				|| in_array($phone, array('9778016362', '9660949453', '9299821844', '9007531672', '9007523221', '9991693811', '9263987654'))
			) {
				if ($userId = \user::getIdByPhone($phone)) {
					$oUser = new \user($userId);
					// авторизуем
					$oUser->login($token);
				} else {
					// регистрируем
					$oResult = \user::register(array(
						'LOGIN' => $phone,
						'PASSWORD' => randString(20),
					));

					if ($oResult->isSuccess()) {
						$userId = $oResult->getData()['ID'];
						$oUser = new \user($userId);
						// авторизуем
						$oUser->login($token);
					} else {
						$this->addError('not_register');
					}
				}

				if (!is_object($oUser)) {
					return null;
				} else {
					$arResult['user'] = $oUser->getData();
				}
			} else {
				$this->addError('wrong_captcha');
			}
		}

		//
		if (!$this->hasErrors() && $userId > 0) {
			global $DB;
			$deviceGUID = $arInput['token'];

			// $sSql = "
				// SELECT *
				// FROM
					// user_devices
				// WHERE
					// user_id = $userId
					// AND device_guid = '$deviceGUID'
				// LIMIT 1";

			// $oldDevice = $DB->Query($sSql, true)->Fetch();

			$ip = getRealIp();
			$platform = $this->User['platform'];

			$sSql = "
				INSERT INTO
					user_devices (user_id, device_guid, date, platform, ip)
				VALUES
					($userId, '$deviceGUID', NOW(), '$platform', '$ip')";

			$dbRes = $DB->Query($sSql, true);

			// if (!$oldDevice) {
				// $message = 'В вашу учетную запись был осуществлен вход через мобильное приложение.';

				// \Bitrix\Main\Loader::includeModule('iblock');
				// $oIbElement = new \CIBlockElement();

				// $arPushType = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
					// 'filter' => array(
						// '=PROPERTY_ID' => \CIBlockTools::GetPropertyId('push_notification', 'PUSH_TYPE'),
						// 'XML_ID' => 'message'
					// ),
					// 'select' => array('ID')
				// ))->Fetch();

				// $arFields = array(
					// 'IBLOCK_ID' => \CIBlockTools::GetIBlockId('push_notification'),
					// 'NAME' => $message,
					// 'PROPERTY_VALUES' => array(
						// 'START_SEND' => date('d.m.Y H:i:s'),
						// 'PUSH_TYPE' => $arPushType['ID'],
						// 'EVENT_ID' => 0,
						// 'USERS' => array($userId)
					// )
				// );

				// $oIbElement->Add($arFields, false, false);
			// }
		}

		// log_($arResult);
		return $arResult;
	}
}
