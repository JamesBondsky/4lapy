<?php

namespace FourPaws\MobileApiOldBundle;

class User
{
	function funOnBeforeUserLogin(&$arFields)
	{
		// отменяем объединение корзин битриксом при авторизации пользователя
		// перед авторизацией проверяем корзины не авторизованного и будущего авторизованного пользователя
		// и, если в них есть товары - сохраняем в сессионную переменную корзину не авторизованного
		if(isset($_SESSION["BASKET_MERGE"]))
		{
			unset($_SESSION["BASKET_MERGE"]);
		}

		if(\CModule::IncludeModule("sale"))
		{
			// определяем будущего пользователя по введённому email"у
			$rsUser = \CUser::GetByLogin($arFields["LOGIN"]);

			if($arUser = $rsUser->Fetch())
			{
				// выясняем его FUSER_ID
				if($arFUser = CSaleUser::GetList(array("USER_ID" => $arUser["ID"])))
				{
					// проверяем его корзину
					$arFilter = array(
						"FUSER_ID" => $arFUser["ID"],
						// "LID" => SITE_ID,
						"ORDER_ID" => "NULL",
						"CAN_BUY" => "Y",
					);

					$arSelect = array(
						"ID",
					);

					$res = CSaleBasket::GetList(array("ID" => "ASC"), $arFilter, false, array("nTopCount" => 1), $arSelect);

					// если его корзина не пустая - сохраняем корзину не авторизованного пользователя
					if($ar_res = $res->GetNext())
					{
						$arFilter = array(
							"FUSER_ID" => CSaleBasket::GetBasketUserID(),
							// "LID" => SITE_ID,
							"ORDER_ID" => "NULL",
							"CAN_BUY" => "Y",
						);

						$arSelect = array(
							"ID",
							"PRODUCT_ID",
							"QUANTITY"
						);

						$res = CSaleBasket::GetList(array("PRODUCT_ID" => "ASC"), $arFilter, false, false, $arSelect);

						while ($ar_res = $res->GetNext())
						{
							$_SESSION["BASKET_MERGE"]["BASKET"][$ar_res["ID"]] = array(
								"PRODUCT_ID" => $ar_res["PRODUCT_ID"],
								"QUANTITY" => $ar_res["QUANTITY"],
							);
						}

						if(count($_SESSION["BASKET_MERGE"]["BASKET"]) > 0)
						{
							$_SESSION["BASKET_MERGE"]["FUSER_ID"] = CSaleBasket::GetBasketUserID();
							$_SESSION["BASKET_MERGE"]["SHOW"] = "Y";
							$_SESSION["BASKET_MERGE"]["REDIRECT"] = "N";
						}
					}
				}
			}
		}
	}

	function funOnAfterUserLogout(&$arParams)
	{
		// при разлогинивании пользователя, удаляем запомненную корзину (если её ещё не удалили до этого)
		if(isset($_SESSION["BASKET_MERGE"]))
		{
			unset($_SESSION["BASKET_MERGE"]);
		}
	}

	function OnAfterUserAddHandler(&$arFields)
	{
		// при добавлении нового пользователя, дёргаем ES для отправки письма о регистрации
		if($arFields["ID"] > 0 and ($_SESSION["USER_REGISTER_PAGE"] != "basket") and (stristr($arFields['EMAIL'], '@register.phone') === false))
		{
			global $USER;
			$arUser = $USER->GetByID($arFields["ID"])->Fetch();

			// добавляем пользователя в список ExpertSender
			$objES = new APIExpertSender(API_EXPERTSENDER_KEY);

			$arFieldsES = array(
				"Mode" => "AddAndUpdate",
				"Force" => false,
				"ListId" => (($_SESSION["USER_REGISTER_PAGE"] != "basket") ? (($_SESSION["USER_REGISTER_PAGE"] == "bonus") ? "52" : "5") : "4"),
				"Email" => $arUser["EMAIL"],
				"Ip" => MyClasses::get_remote_ip(),
				"Firstname" => $arUser["NAME"],
				"Lastname" => $arUser["LAST_NAME"],
				"Properties" => array(
					array(
						"Id" => "10",
						"Value" => fUserForcedAuthorization($arUser["ID"]),
					),
					array(
						"Id" => "11",
						"Value" => $arUser["ID"],
					),
					array(
						"Id" => "12",
						"Value" => ($_SESSION["USER_REGISTER_PAGE"] ?: "basket"),
					),
					array(
						"Id" => "29",
						"Value" => (($_SESSION["USER_REGISTER_PAGE"] != "basket") ? (($_SESSION["USER_REGISTER_PAGE"] == "bonus") ? "http://bonus.4lapy.ru/" : "http://4lapy.ru/auth/register.php") : "http://4lapy.ru/personal/cart/"),
					),
				),
			);
			if ($_REQUEST["i_want_newsletter"] == "on")
			{
				$arFieldsES["Properties"][] = array("Id" => "23","Value" => "1");
			}
			if ($arUser['PASSWORD'])
			{
				$arFieldsES["Properties"][] = array("Id" => "31","Value" => $arUser['PERSONAL_PHONE']);
			}
			// echo "<pre>";print_r($arFieldsES);echo "</pre>"."\r\n";
			// $objES->AddSubscribers($arFieldsES);
		}
		elseif ($arFields["ID"] > 0 and !(stristr($arFields['EMAIL'], '@register.phone') === false))
		{
			global $USER;
			$arUser = $USER->GetByID($arFields["ID"])->Fetch();
			if($_SESSION["USER_REGISTER_PAGE"] == 'phone')
			{
				$text = '';
			}
			elseif($_SESSION["USER_REGISTER_PAGE"] == 'ml_msk')//для регистрации пользователей Москвы из МЛ
			{
				$text = 'Акция! Заполните свой личный кабинет на сайте '.get_auth_url_for_sms($arFields["ID"]).' до 16.06 и получите 100 бонусов на карту.';
			}
			elseif($_SESSION["USER_REGISTER_PAGE"] == 'ml_rus')//для регистрации пользователей России из МЛ
			{
				$text = 'Мы создали вам личный кабинет на сайте 4lapy.ru . Логин: '.$arUser['PERSONAL_PHONE'].' Пароль: '.$arUser['PERSONAL_PHONE'].'. Заполните поле email до 10.06 и получите 100 бонусов на бонусную карту.';
			}
			else
			{
				$text = 'Спасибо за регистрацию на сайте 4lapy.ru! Теперь вам доступны все возможности нашего личного кабинета!';
			}
			if(strlen($text)>0){
				$rrr = MyCUtils::SendSMS($arUser['LOGIN'], $text);
			}
			if($_SESSION["USER_REGISTER_PAGE"] == 'ml_msk' or $_SESSION["USER_REGISTER_PAGE"] == 'ml_rus')
			{
				// log_($rrr);
				// log_($arUser['LOGIN']);
				// log_($text);
			}
		}

		//счетчик регистраций
		if($arFields["ID"] > 0)
		{
			global $DB;
			$sSql = "
				SELECT
					ID,
					count
				FROM
					bx_reg
				WHERE
					Id='1'";

			if($res = $DB->Query($sSql, true))
			{
				if ($ar_res = $res->Fetch())
				{
					//обновляем
					$sSql = "
						UPDATE
							bx_reg
						SET
							`count`='".($ar_res['count']+1)."'
						WHERE
							`Id`='".$ar_res['ID']."'";

					$DB->StartTransaction();

					if($res = $DB->Query($sSql, true))
					{
						$DB->Commit();
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		//!счетчик регистраций
	}

	function funOnBeforeUserAdd(&$arFields)
	{
		// корректируем номер телефона (10 цифр без начальных 7/+7/8)
		if (isset($arFields['PERSONAL_PHONE'])) {
			$arFields['PERSONAL_PHONE'] = substr(preg_replace('/\D/', '', $arFields['PERSONAL_PHONE']), -10);
		}
		global $APPLICATION;
		$arUser = \CUser::GetByID($arFields['ID'])->Fetch();
		
		//проверяем учетные данные на уникальность мыла и телефона, проверяем всех, кроме быстрых заказов
		if($arFields['EMAIL'] and ($arUser['EMAIL'] != $arFields['EMAIL'])){
			$checkEmail = MyCAjax::CheckEmail($arFields['EMAIL']);
			if (!$checkEmail['result']) {
				switch ($checkEmail['error']) {
					case 'is used':
						$APPLICATION->throwException('Указанный Email уже используется на сайте', 'EMAIL');
						return false;
						break;
					case 'not valid':
						$APPLICATION->throwException('Введите корректный Email', 'EMAIL');
						return false;
						break;
					default:
						break;
				}
			}
		}
		
		
		$isFastOrder = stristr($arFields['EMAIL'], '@fastorder.ru');

		if(!$isFastOrder){
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth();
			if ($oBxmodAuth->GetUserByPhone($arFields['PERSONAL_PHONE'])) {
				$APPLICATION->throwException('Пользователь с таким телефоном уже зарегистрирован', 'PERSONAL_PHONE');
				return false;
			}
		}
		//!проверяем учетные данные на уникальность мыла и телефона, проверяем всех, кроме быстрых заказов
	}

	function funOnBeforeUserUpdate(&$arFields)
	{
		// корректируем номер телефона (10 цифр без начальных 7/+7/8)
		if (isset($arFields['PERSONAL_PHONE'])) {
			$arFields['PERSONAL_PHONE'] = substr(preg_replace('/\D/', '', $arFields['PERSONAL_PHONE']), -10);
		}

		global $APPLICATION;

		$arUser = \CUser::GetByID($arFields['ID'])->Fetch();

		//проверяем учетные данные на уникальность мыла и телефона, проверяем всех, кроме быстрых заказов
		if($arFields['EMAIL'] and ($arUser['EMAIL'] != $arFields['EMAIL'])){
			$checkEmail = MyCAjax::CheckEmail($arFields['EMAIL']);
			
			if (!$checkEmail['result']) {
				switch ($checkEmail['error']) {
					case 'is used':
						$APPLICATION->throwException('Указанный Email уже используется на сайте', 'EMAIL');
						return false;
						break;
					case 'not valid':
						$APPLICATION->throwException('Введите корректный Email', 'EMAIL');
						return false;
						break;
					default:
						break;
				}
			}
		}
		
		if($arFields['PERSONAL_PHONE'] and ($arUser['PERSONAL_PHONE'] != $arFields['PERSONAL_PHONE'])){
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth();
			if ($oBxmodAuth->GetUserByPhone($arFields['PERSONAL_PHONE'])) {
				$APPLICATION->throwException('Пользователь с таким телефоном уже зарегистрирован', 'PERSONAL_PHONE');
				return false;
			}
		}
		//!проверяем учетные данные на уникальность мыла и телефона, проверяем всех, кроме быстрых заказов
	}

	//проверяем юзера на привязку к группам "Делал заказы в МП" и "Не делал заказы в МП"
	function checkMakingOrderInMPGroups($user_id)
	{
		// log_('checkMakingOrderInMPGroups');
		// log_($user_id);
		$arUserGroups = \CUser::GetUserGroup($user_id);
		// log_(array($user_id, $arUserGroups));
		if(in_array(MP_ORDERS_EXIST_GROUP, $arUserGroups) or in_array(MP_ORDERS_NOT_EXIST_GROUP, $arUserGroups))
		{
			return true;
		}
		return false;
	}

	//устанавливает привязку к группе "Делал заказы в МП"
	function setMakingOrderInMP($user_id)
	{
		// log_('setMakingOrderInMP');
		// log_($user_id);
		$arUserGroups = \CUser::GetUserGroup($user_id);
		// log_(array($user_id, $arUserGroups));
		if(!in_array(MP_ORDERS_EXIST_GROUP, $arUserGroups))
		{
			$arUserGroups[] = MP_ORDERS_EXIST_GROUP;

			$arUserGroups = array_flip($arUserGroups);
			unset($arUserGroups[MP_ORDERS_NOT_EXIST_GROUP]);
			$arUserGroups = array_flip($arUserGroups);
			
			// log_($arUserGroups);
			\CUser::SetUserGroup($user_id, $arUserGroups);
		}
	}

	//устанавливает привязку к группе "Не делал заказы в МП"
	function setNotMakingOrderInMP($user_id)
	{
		// log_('setNotMakingOrderInMP');
		// log_($user_id);
		$arUserGroups = \CUser::GetUserGroup($user_id);
		// log_(array($user_id, $arUserGroups));
		if(!in_array(MP_ORDERS_NOT_EXIST_GROUP, $arUserGroups))
		{
			$arUserGroups[] = MP_ORDERS_NOT_EXIST_GROUP;

			$arUserGroups = array_flip($arUserGroups);
			unset($arUserGroups[MP_ORDERS_EXIST_GROUP]);
			$arUserGroups = array_flip($arUserGroups);

			// log_($arUserGroups);
			\CUser::SetUserGroup($user_id, $arUserGroups);
		}
	}

	function checkOrdersFromMP($user_id)
	{
		// log_('checkOrdersFromMP');
		// log_($user_id);
		\CModule::IncludeModule("sale");
		// выбираем невыгруженные заказы
		$arFilter = Array(
			"USER_ID" => $user_id,
			"PROPERTY_VAL_BY_CODE_fromAPP" => 'Y',
			);
		$db_sales = \CSaleOrder::GetList(
			array("DATE_INSERT" => "ASC"), 
			$arFilter, 
			false, 
			false, 
			array('ID')
			);

		return $db_sales->SelectedRowsCount();
	}

	//проверяем юзера на привязку к группам "Делал заказы в МП" и "Не делал заказы в МП"
	function setBreedersGroups($user_id, $isEmployeeBreeder = false)
	{
		$arUserGroups = \CUser::GetUserGroup($user_id);
		//если юзер в группе
		if(in_array(BREEDERS_GROUP, $arUserGroups))
		{
			//если в МЛ он питомник
			if($isEmployeeBreeder)
			{
				return true;
			}
			else
			{
				//убираем из группы питомников
				$arUserGroups = array_flip($arUserGroups);
				unset($arUserGroups[BREEDERS_GROUP]);
				$arUserGroups = array_flip($arUserGroups);
				\CUser::SetUserGroup($user_id, $arUserGroups);
				\CUser::SetUserGroupArray($arUserGroups);

				return true;
			}
		}
		else
		{
			if($isEmployeeBreeder)
			{
				//добавляем в группу питомников
				$arUserGroups[] = BREEDERS_GROUP;
				\CUser::SetUserGroup($user_id, $arUserGroups);
				\CUser::SetUserGroupArray($arUserGroups);

				return true;
			}
			else
			{
				return true;
			}
		}
	}

	function SendSmsBonusPage($pass, $userId, $login)
	{
		$text = 'Спасибо за регистрацию на сайте 4lapy.ru! Теперь Вам доступны все возможности личного кабинета! Номер вашего телефона является логином, пароль для доступа '.$pass.'. Для авторизации перейдите по ссылке '.get_auth_url_for_sms($userId);
		$rrr = MyCUtils::SendSMS($login, $text);
		return $rrr;
	}

	function UpdateUserPhone($userId, $phone)
	{
		$userEdit = new \CUser;
		$result = $userEdit->Update($userId, array('PERSONAL_PHONE' => $phone));
		return $result;
	}

	// проверяем, заходил ли раньше пользователь с текущего устройства/браузера
	function onAfterUserLogin(&$arParams)
	{
		if($arParams['USER_ID'] > 0 && stristr($arParams['LOGIN'], '@fastorder.ru') === false)
		{
			global $APPLICATION;
			global $DB;

			$deviceGUID = $APPLICATION->get_cookie('DUUID'.$arParams['USER_ID']);
			$oldDevice = false;

			if (!preg_match('/^[a-z0-9]{8}-[a-z0-9]{4}-4[a-z0-9]{3}-[89ab][a-z0-9]{3}-[a-z0-9]{12}$/i', $deviceGUID))
			{
				$deviceGUID = '';
			}

			if (strlen($deviceGUID))
			{
				$sSql = "
					SELECT IF(COUNT(1)>0,1,0) OLD
					FROM
						user_devices
					WHERE
						user_id = {$arParams['USER_ID']}
						AND device_guid = '$deviceGUID'
					LIMIT 1";

				$oldDevice = $DB->Query($sSql, true)->Fetch();
			}

			if (strlen($deviceGUID) == 0)
			{
				if (function_exists('com_create_guid'))
				{
					$deviceGUID = com_create_guid();
				}
				else
				{
					$data = openssl_random_pseudo_bytes(16);
					$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
					$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
					$deviceGUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
				}
				$deviceGUID = $deviceGUID;
			}

			$ip = null;
			if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif(!empty($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			$platform = $DB->ForSql($_SERVER['HTTP_USER_AGENT']);

			$sSql = "
				INSERT INTO
					user_devices (user_id, device_guid, date, platform, ip)
				VALUES
					({$arParams['USER_ID']}, '$deviceGUID', NOW(), '$platform', '$ip')";

			$dbRes = $DB->Query($sSql, true);

			if ((!$oldDevice || !$oldDevice['OLD']) && $dbRes)
			{
				// для отключения смс о входе
				// // разослать сообщения
				// $arUser = \Lapy\Push\SessionTable::getList(array(
				// 	'filter' => array('=USER_ID' => $arParams['USER_ID']),
				// 	'select' => array('USER_ID')
				// ))->fetch();

				// $message = GetMessage('NEW_DEVICE_LOGIN_TEXT');
				// if ($arUser['USER_ID'])
				// {
				// 	// если есть мобильное приложение
				// 	$oIbElement = new \CIBlockElement();

				// 	$arPushType = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
				// 		'filter' => array(
				// 			'=PROPERTY_ID' => \CIBlockTools::GetPropertyId('push_notification', 'PUSH_TYPE'),
				// 			'XML_ID' => 'message'
				// 		),
				// 		'select' => array('ID')
				// 	))->Fetch();

				// 	$arFields = array(
				// 		'IBLOCK_ID' => \CIBlockTools::GetIBlockId('push_notification'),
				// 		'NAME' => $message,
				// 		'PROPERTY_VALUES' => array(
				// 			'START_SEND' => date('d.m.Y H:i:s'),
				// 			'PUSH_TYPE' => $arPushType['ID'],
				// 			'EVENT_ID' => 0,
				// 			'USERS' => array($arParams['USER_ID'])
				// 		)
				// 	);

				// 	$oIbElement->Add($arFields, false, false);
				// }
				// else
				// {
				// 	$arUser = CUser::GetByID($arParams['USER_ID'])->Fetch();

				// 	\MyCUtils::SendSMS($arUser['PERSONAL_PHONE'], $message);
				// }
				// !для отключения смс о входе

				// поставить куку в далекое будущее
				$APPLICATION->set_cookie('DUUID'.$arParams['USER_ID'], $deviceGUID, pow(2, 31) - 1, '/', false, false, true, false, true);
			}
		}
	}

}
