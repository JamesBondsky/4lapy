<?

namespace FourPaws\MobileApiOldBundle;

class Card
{
	static public $LAST_ERROR = false;

	function ValidateCard_ml($card, $userId = 0)
	{
		// валидация карты на сайте
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		$arUser = CUser::GetList(
			($by="id"),
			($order="desc"),
			array(
				"UF_DISC" => ($card ?: 0),
				"!ID" => ($userId ?: $USER->GetID())
			),
			array(
				"SELECT" => array(
					"UF_DISC"
				),
				"NAV_PARAMS" => array(
					"nTopCount" => 1
				)
			)
		)->Fetch();

		if($arUser)
		{
			self::$LAST_ERROR = array(
				"code" => 3,
				"description" => "is used on site",
				"text" => "Карта уже используется пользователем сайта"
			);
			return false;
		}
		else
		{
			// валидация карты в ML

			try
			{
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5);
				$params = array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
					);
				$client = new SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login" => API_ML_LOGIN,
						"password" => API_ML_PASSWORD,
						"ip" => IP_ADDRESS,
						"innerLogin" => $USER->GetLogin()
					)
				);
			}
			catch(SoapFault $e)
			{
				return self::ValidateCard_old($card, $userId);
			}
			catch(Exception $e)
			{
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID" => "",
					"ITEM_ID" => "Авторизация",
					"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
				));

				self::$LAST_ERROR = array(
					"code" => $e->detail->details->code,
					"description" => $e->detail->details->description,
					"text" => "Ошибка ML авторизации"
				);
				// return false;
				return self::ValidateCard_old($card, $userId);
			}
			// получаем информацию по карте
			$objXml = new CDataXML();

			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "card_validate",
						"parameters" => array(
							array("Name" => "cardnumber", "Value" => $card),
						),
					)
				)->ExecuteResult->Value
			);

			// если карта не валидна
			if(!$objXml->SelectNodes("/cardvalidateresult/isvalid")->textContent())
			{
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID" => "",
					"ITEM_ID" => $card,
					"DESCRIPTION" => $objXml->SelectNodes("/cardvalidateresult/validationresult")->textContent(),
				));

				self::$LAST_ERROR = array(
					"code" => $objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent(),
					"description" => ($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == "1" ? "not found" : "is used on ml"),
					"text" =>$objXml->SelectNodes("/cardvalidateresult/validationresult")->textContent() . '. Обратитесь на горячую линию.'
				);
				return false;
			}
		}

		return true;
	}

	function ValidateCard_old($card, $userId = 0)
	{
		// валидация карты на сайте
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		$arUser = CUser::GetList(
			($by="id"),
			($order="desc"),
			array(
				"UF_DISC" => ($card ?: 0),
				"!ID" => ($userId ?: $USER->GetID())
			),
			array(
				"SELECT" => array(
					"UF_DISC"
				),
				"NAV_PARAMS" => array(
					"nTopCount" => 1
				)
			)
		)->Fetch();

		if($arUser)
		{
			self::$LAST_ERROR = array(
				"code" => 3,
				"description" => "is used on site",
				"text" => "Карта уже используется пользователем сайта"
			);
			return false;
		}
		else
		{
			//если манзана свалилась - возьмем скидку и баланс из базы
			global $DB;
			$sSql = "
				SELECT 
					ID,
					number,
					discount,
					balance
				FROM 
					p_cards 
				WHERE 
					number='".$card."'";

			if($res = $DB->Query($sSql, true))
			{
				if (!($ar_res = $res->Fetch()))
				{
					// если карта не валидна
					self::$LAST_ERROR = array(
						"code" => 1,
						"description" => "not found",
						"text" => "Карта не найдена"
					);
					return false;
				}
			}
			//!если манзана свалилась - возьмем скидку и баланс из базы
		}

		return true;
	}

	function ValidateCard($card, $userId = 0, $ml = NULL)
	{
		return ((is_null($ml) ? MANZANA : $ml) ? self::ValidateCard_ml($card, $userId) : self::ValidateCard_old($card, $userId));
	}

	function UpdateDataCard_ml($card, $bReturnData=false)
	{
		// обновление данных карты в сессии
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			$objXml = new CDataXML();
			try
			{
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5); 
				$params = array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
					);
				$client = new \SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login" => API_ML_LOGIN,
						"password" => API_ML_PASSWORD,
						"ip" => IP_ADDRESS,
						"innerLogin" => $USER->GetLogin()
					)
				);
			}
			catch(\SoapFault $e)
			{
				return self::UpdateDataCard_old($card, $bReturnData);
			}
			catch(\Exception $e)
			{
				// запишем в журнал событий
				\CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID" => "",
					"ITEM_ID" => "Авторизация",
					"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
				));

				self::$LAST_ERROR = array(
					"code" => $e->detail->details->code,
					"description" => $e->detail->details->description,
					"text" => "Ошибка ML авторизации"
				);

				// //если манзана свалилась - возьмем скидку и баланс из базы
				// global $DB;
				// $sSql = "
				// 	SELECT 
				// 		ID,
				// 		number,
				// 		discount,
				// 		balance
				// 	FROM 
				// 		p_cards 
				// 	WHERE 
				// 		number='".$card."'";

				// if($res = $DB->Query($sSql, true))
				// {
				// 	if ($ar_res = $res->Fetch())
				// 	{
				// 		// сохраняем данные карты в сессию
				// 		$_SESSION["CARD"] = array(
				// 			"NUMBER" => $ar_res["number"],
				// 			"BALANCE" => $ar_res["balance"],
				// 			"DISCOUNT" => $ar_res["discount"],
				// 			"TYPE" => ($ar_res["balance"])?"1":"0",
				// 		);
				// 	}
				// }
				// //!если манзана свалилась - возьмем скидку и баланс из базы

				// return false;
				return self::UpdateDataCard_old($card, $bReturnData);
			}
			// валидация карты в ML
			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "card_validate",
						"parameters" => array(
							array("Name" => "cardnumber", "Value" => $card),
						),
					)
				)->ExecuteResult->Value
			);

			// если карта привязана в ML
			if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == 2)
			{
				// // ищем юзера, привязанного к карте
				// $objXml->LoadString(
				// 	$client->Execute(
				// 		array(
				// 			"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
				// 			"contractName" => "client_search",
				// 			"parameters" => array(
				// 				array("Name" => "maxresultsnumber", "Value" => "1"),
				// 				array("Name" => "cardnumber", "Value" => $card),
				// 			),
				// 		)
				// 	)->ExecuteResult->Value
				// );

				// ищем юзера, привязанного к карте
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "search_cards_by_number",
							"parameters" => array(
								array("Name" => "cardnumber", "Value" => $card),
							),
						)
					)->ExecuteResult->Value
				);

				//тут будем помещать или вытаскивать юзера из группы со скидками для Питомников
				$EmployeeBreederCode = $objXml->SelectNodes("/Cards/Card/EmployeeBreeder")->textContent();
				$isEmployeeBreeder = in_array($EmployeeBreederCode, array(2,3));
				MyCUser::setBreedersGroups($USER->GetID(), $isEmployeeBreeder);
				//!тут будем помещать или вытаскивать юзера из группы со скидками для Питомников

				$cId = $objXml->SelectNodes("/Cards/Card/contactid")->textContent();

				// получаем данные карты
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "cards",
							"parameters" => array(
								// array("Name" => "contact_id", "Value" => $objXml->SelectNodes("/Clients/Client/contactid")->textContent()),
								array("Name" => "contact_id", "Value" => $objXml->SelectNodes("/Cards/Card/contactid")->textContent()),
							),
						)
					)->ExecuteResult->Value
				);

				if($objXml->SelectNodes("/Cards/Card") and $objXml->SelectNodes("/Cards/Card/pl_status_text")->textContent() == 'Active'){
					$aCardData=array(
						"ID"=>$objXml->SelectNodes("/Cards/Card/pl_bonuscardid")->textContent(),
						"TYPE"=>$objXml->SelectNodes("/Cards/Card/pl_bonustype")->textContent(),
						"TYPE_TEXT"=>$objXml->SelectNodes("/Cards/Card/pl_bonustype_text")->textContent(),
						"NUMBER"=>$objXml->SelectNodes("/Cards/Card/pl_number")->textContent(),
						"STATUS"=>$objXml->SelectNodes("/Cards/Card/pl_status_text")->textContent(),
						"BALANCE"=>$objXml->SelectNodes("/Cards/Card/pl_active_balance")->textContent(),
						"DISCOUNT"=>intval($objXml->SelectNodes("/Cards/Card/pl_discount")->textContent()),
						"SUMM"=>$objXml->SelectNodes("/Cards/Card/pl_summdiscounted")->textContent(),
						"CREDIT"=>$objXml->SelectNodes("/Cards/Card/pl_credit")->textContent(),
						"DEBET"=>$objXml->SelectNodes("/Cards/Card/pl_debet")->textContent(),
					);

					// исправляем тип карты
					// товарищи из манзаны гарантируют: не нулевой pl_debet означает, что карта бонусная
					if($objXml->SelectNodes("/Cards/Card/pl_debet")->textContent() > 0){
						$aCardData['TYPE']=1;
						$aCardData['TYPE_TEXT']='Bonus';
					}

					//а тут давайте апдейтить юзера, т.е. дописывать к нему актуальный номер карты
					$actual_card = $objXml->SelectNodes("/Cards/Card/pl_number")->textContent();
					if($actual_card != $card)
					{
						$objUser = new \CUser;
						$objUser->Update(
							$USER->GetID(),
							array(
								"UF_DISC" => $actual_card
							)
						);
					}
					//!а тут давайте апдейтить юзера, т.е. дописывать к нему актуальный номер карты

					//добавляем расширенный баланс
					$objXml->LoadString(
						$client->Execute(
							array(
								"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
								"contractName" => "advanced_balance",
								"parameters" => array(
									array("Name" => "contact_id", "Value" => $cId),
								),
							)
						)->ExecuteResult->Value
					);

					$arCards = array();

					if($node = $objXml->SelectNodes('/Cards/Card/')){
						foreach ($node->children() as $key => $value){
							if($value->name == 'Card'){
								foreach ($value->children() as $key2 => $value2){
									$arCards[$key][$value2->name] = $value2->content;
								}
							}
						}
					}
					foreach ($arCards as $arCard) {
						$arCardsF[$arCard['CardNumber']] = $arCard;
					}

					$aCardData["BALANCE"] = $arCardsF[$actual_card]["BalanceBase"];
					//!добавляем расширенный баланс

					// сохраняем данные карты в сессию
					if(!$bReturnData){
						$_SESSION["CARD"]=$aCardData;
					}else{
						return($aCardData);
					}
				}else{
					$objUser = new \CUser;
					$objUser->Update(
						$USER->GetID(),
						array(
							"UF_DISC" => ''
						)
					);
					if(!$bReturnData){
						$_SESSION["CARD"]=false;
					}else{
						return(false);
					}
				}
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	function UpdateDataCard_old($card, $bReturnData=false){
		// обновление данных карты в сессии
		global $USER;
		self::$LAST_ERROR=false;
		$card=filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card){
			//если манзана свалилась - возьмем скидку и баланс из базы
			global $DB;
			$sSql="
				SELECT 
					ID,
					number,
					discount,
					balance
				FROM 
					p_cards 
				WHERE 
					number='".$card."'";
			if($res=$DB->Query($sSql, true)){
				$aCardData=array();
				if($ar_res=$res->Fetch()){
					$aCardData=array(
						"NUMBER"=>$ar_res["number"],
						"BALANCE"=>$ar_res["balance"],
						"DISCOUNT"=>$ar_res["discount"],
						"TYPE"=>($ar_res["balance"])?"1":"",
						"TYPE_TEXT"=>($ar_res["balance"])?"Bonus":"",
					);
				}
				// сохраняем данные карты в сессию
				if(!$bReturnData){
					$_SESSION["CARD"]=$aCardData;
					return(true);
				}else{
					return($aCardData);
				}
			}
			//!если манзана свалилась - возьмем скидку и баланс из базы
		}
		return(false);
	}

	function SetDataCard($card, $userID = 0)
	{
		global $USER;

		if(MANZANA)
		{
			$result = self::UpdateDataCard_ml($card);

			// // работаем с внутренним счётом пользователя
			// if($USER->IsAuthorized())
			// {
            \CModule::IncludeModule('sale');

            $arSaleUserAccount = \CSaleUserAccount::GetByUserID(($userID)?:($USER->GetID()), "RUB");

            // если карта привязана и карта бонусная
            // if(($arCard = self::GetDataCard()) and ($arCard["TYPE"] == 1))
            // if(($arCard = self::UpdateDataCard_ml($card, true)) and ($arCard["TYPE"] == 1))
            if($arCard = self::UpdateDataCard_ml($card, true))
            {
                // заводим/обновляем бонусный счёт, записываем в транзакции
                if(!$arSaleUserAccount)
                {
                    $result = \CSaleUserAccount::Add(
                        array(
                            "USER_ID" => ($userID)?:($USER->GetID()),
                            "CURRENCY" => "RUB",
                            "CURRENT_BUDGET" => $arCard["BALANCE"],
                        )
                    );

                    if($result)
                    {
                        \CSaleUserTransact::Add(
                            array(
                                "USER_ID" => ($userID)?:($USER->GetID()),
                                "AMOUNT" =>  $arCard["BALANCE"],
                                "CURRENCY" => "RUB",
                                "DEBIT" => "Y",
                                "DESCRIPTION" => "Синхронизация с Manzana Loyalty",
                                "TRANSACT_DATE" => ConvertTimeStamp(false, "FULL")
                            )
                        );
                    }
                }
                else
                {
                    $result = \CSaleUserAccount::Update(
                        $arSaleUserAccount["ID"],
                        array(
                            "CURRENT_BUDGET" => $arCard["BALANCE"],
                        )
                    );

                    if($result)
                    {
                        \CSaleUserTransact::Add(
                            array(
                                "USER_ID" => ($userID)?:($USER->GetID()),
                                "AMOUNT" =>  abs($arSaleUserAccount["CURRENT_BUDGET"] - $arCard["BALANCE"]),
                                "CURRENCY" => "RUB",
                                "DEBIT" => (($arSaleUserAccount["CURRENT_BUDGET"] > $arCard["BALANCE"]) ? "N" : "Y"),
                                "DESCRIPTION" => "Синхронизация с Manzana Loyalty",
                                "TRANSACT_DATE" => ConvertTimeStamp(false, "FULL")
                            )
                        );
                    }
                }
            }
			// }
		}
		else
		{
			$result = self::UpdateDataCard_old($card);
		}

		return $result;
	}

	function GetDataCard()
	{
		return $_SESSION["CARD"];
	}

	function RemoveDataCard()
	{
		global $USER;

		if(isset($_SESSION["CARD"]))
		{
			unset($_SESSION["CARD"]);
		}
	}

	function OnAfterUserLogoutHandler(&$arParams)
	{
		// при разлогинивании пользователя, удаляем из сессии данные карты
		self::RemoveDataCard();
	}

	function OnAfterUserLoginHandler_($arFields)
	{
		$arFields["USER_ID"] = $arFields["user_fields"]["ID"];
		MyCCard::OnAfterUserLoginHandler($arFields);
	}
	
	function OnAfterUserLoginHandler($arFields)
	{
		CModule::IncludeModule("iblock");
		$el = new CIBlockElement;
		// при успешной авторизации получаем данные карты из ML и записываем в сессию/привязываем карту
		if($arFields["USER_ID"] > 0)
		{
			$arUser = CUser::GetByID($arFields["USER_ID"])->Fetch();

			// если у пользователя имеется привязанная карта
			if($arUser["UF_DISC"])
			{
				// если уже имеются данные карты и они не совпадают с привязанной картой - выводим предупреждение
				if(($arCard = MyCCard::GetDataCard()) and ($arCard["NUMBER"] != $arUser["UF_DISC"]))
				{
					$_SESSION["WARNING"]["HAS_ATTACHED_CARD"] = "Y";
					$_SESSION["WARNING"]["CARD_NUMBER"] = $arCard["NUMBER"];
				}

				// обновляем её данные
				self::SetDataCard($arUser["UF_DISC"]);

				$arLoadProductArray = Array(
					"IBLOCK_ID"      => 54,
					"NAME"           => $arUser["UF_DISC"],
					"CODE"           => $arUser["UF_DISC"],
					"ACTIVE"         => "Y",
					);

				$REQUEST_ID = $el->Add($arLoadProductArray);
			}
			else
			{
				// если у юзера не привязана карта, а в сессии записаны данные карты - привязываем её
				if($arCard = self::GetDataCard())
				{
					$objUser = new CUser;
					$objUser->Update(
						$arFields["USER_ID"],
						array(
							"UF_DISC" => $arCard["NUMBER"]
						)
					);

					$arLoadProductArray = Array(
						"IBLOCK_ID"      => 54,
						"NAME"           => $arCard["NUMBER"],
						"CODE"           => $arCard["NUMBER"],
						"ACTIVE"         => "Y",
						);

					$REQUEST_ID = $el->Add($arLoadProductArray);
				}else{
					// log_($arFields);
					if (!(stristr($arUser['EMAIL'], '@fastorder.ru')) and $arUser['PERSONAL_PHONE']) {
						$vbCard = self::GetVBC($arFields);
						$objUser = new CUser;
						$objUser->Update(
							$arFields["USER_ID"],
							array(
								"UF_DISC" => $vbCard
							)
						);
						self::SetDataCard($vbCard);
					}
				}

				//от тут попробуем всунуть функционал ВБК - виртуальной бонусной карты
				//т.е. при логине юзера будем спрашивать в МЛ наличие карты для этого номера телефона
				//если карты нет - выдадим виртуальную
				//говорят тут есть какой-то функционал по получению актуальной карты для юзера...
				//так. пришел Вася без карты, залогинился, дальше:
				//надо послать какой-то запрос в мл...
				// self::GetVBC($arFields);
			}
		}
	}

	function OnBeforeUserAddOrUpdateHandler(&$arFields)
	{
		if($arFields['NAME']){
			$arFields['NAME'] = preg_replace("/[^a-zA-ZА-Яа-я\s]/u","",$arFields['NAME']);
		}
		if($arFields['LAST_NAME']){
			$arFields['LAST_NAME'] = preg_replace("/[^a-zA-ZА-Яа-я\s]/u","",$arFields['LAST_NAME']);
		}
		if($arFields['SECOND_NAME']){
			$arFields['SECOND_NAME'] = preg_replace("/[^a-zA-ZА-Яа-я\s]/u","",$arFields['SECOND_NAME']);
		}

		// перед добавлением/обновлением данных пользователя делаем валидацию карты
		if($arFields["UF_DISC"])
		{
			global $APPLICATION;
			// log_('Начали проверку перед регистрацией');
			// если карта не валидна (привязка к пользователю в ML допускается)
			if(!self::ValidateCard($arFields["UF_DISC"], $arFields["ID"]) and (self::$LAST_ERROR["description"] != "is used on ml"))
			{
				// log_('Карта не валидна, и ошибка НЕ *карта используется в мл*');
				if(in_array(self::$LAST_ERROR["description"], array("not found", "is used on site")))
				{
					// устанавливаем исключение и отменяем событие
					$APPLICATION->throwException(self::$LAST_ERROR["text"]);
					return false;
				}
				else
				{
					// удаляем карту из параметров
					unset($arFields["UF_DISC"]);
				}
			}
			//если карта есть в МЛ и привязанный к ней там номер телефона не равен введенному номеру
			elseif((self::$LAST_ERROR["description"] == "is used on ml") and (!self::checkUserCardData($arFields, $arFields["UF_DISC"])))
			{
				// log_('А тут уже наш сценарий');
				//проверим дополнительно равенство номера телефона существующего юзера
				global $USER;
				if($arFields["ID"] and ($arUser = $USER->GetByID($arFields['ID'])->Fetch()) and self::checkUserCardData($arUser, $arFields["UF_DISC"]))
				{
					// log_('Попытка найти существующего юзера');
					return true;
				}
				// log_('Устанавливаем ошибку');
				// log_(self::$LAST_ERROR);
				// устанавливаем исключение и отменяем событие
				$APPLICATION->throwException(self::$LAST_ERROR["text"]);
				return false;
			}
			// log_('Вообще в сценарий не попали');

			return true;
		}
	}

	function checkUserCardData($arUser, $CARD)
	{
		$arFieldsCheck = array('phone');

			// self::$LAST_ERROR = array(
			// 	"code" => 3,
			// 	"description" => "is used on site",
			// 	"text" => "Карта уже используется пользователем сайта"
			// );

		global $USER;
		// $arUser = $USER->GetByID($arUser['ID'])->Fetch();
		$arMLUser = array();

		if($CARD)
		{
			$objXml = new CDataXML();
			try
			{
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5);
				$params = array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
					);
				$client = new SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login" => API_ML_LOGIN,
						"password" => API_ML_PASSWORD,
						"ip" => IP_ADDRESS,
						"innerLogin" => $USER->GetLogin()
					)
				);
			}
			catch(SoapFault $e)
			{return false;}
			catch(Exception $e)
			{return false;}

			// преобразуем номер телефона к виду 7xxxxxxxxxx
			$phone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "7$1$2$3$4", $arUser["PERSONAL_PHONE"]);

			// валидация в ML (ещё раз, на всякий случай)
			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "card_validate",
						"parameters" => array(
							array("Name" => "cardnumber", "Value" => $CARD),
						),
					)
				)->ExecuteResult->Value
			);

			// если карта уже привязана в ML - ищем id пользователя
			if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == 2)
			{
				// ищем юзера в ML, привязанного к карте
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "search_cards_by_number",
							"parameters" => array(
								array("Name" => "cardnumber", "Value" => $CARD),
							),
						)
					)->ExecuteResult->Value
				);

				if($node = $objXml->SelectNodes('/Cards/Card/')) 
				{
					foreach ($node->children() as $key => $value) 
					{
						$arMLUser[$value->name] = $value->content;
					}
				}

				foreach ($arFieldsCheck as $field) 
				{
					if($field == 'phone')
					{
						if($phone == $arMLUser['mobilephone'])
						{
							return true;
						}
						else
						{
							self::$LAST_ERROR = array(
								"code" => 66,
								"description" => "is used on site",
								"text" => "Карта уже используется другим пользователем, обратитесь на горячую линию"
							);
							return false;
						}
					}
				}
				// echo "<pre>";print_r($arUser);echo "</pre>"."\r\n";
				// echo "<pre>";print_r($arMLUser);echo "</pre>"."\r\n";
			}
			return true;
		}
	}

	function OnAfterUserAddOrUpdateHandler_ml(&$arFields)
	{
		// log_(array('Входящие параметры в событие', $arFields));
		// если обновление данных пользователя прошло успешно - создаём/обновляем пользователя в ML, привязывает к нему карту
		if($arFields["RESULT"] and ($_SESSION["USER_REGISTER_PAGE"] != 'ml_msk') and ($_SESSION["USER_REGISTER_PAGE"] != 'ml_rus') and ($_SESSION['disableMlUpdate'] != true))
		{
			global $USER;
			$arUser = $USER->GetByID($arFields["ID"])->Fetch();
			// log_(array('Данные по юзеру в битриксе', $arUser));
			if($arUser["UF_DISC"])
			{
				$objXml = new CDataXML();
				try
				{
					// создаём soap-клиент и авторизуемся под админом
					ini_set('default_socket_timeout', 5);
					$params = array(
						'trace' => 1,
						'exceptions'=> 1,
						"connection_timeout" => 5
						);
					$client = new SoapClient(API_ML_WSDL, $params);

					$objAuthenticate = $client->Authenticate(
						array(
							"login" => API_ML_LOGIN,
							"password" => API_ML_PASSWORD,
							"ip" => IP_ADDRESS,
							"innerLogin" => $USER->GetLogin()
						)
					);
				}
				catch(SoapFault $e)
				{

					// добавляем номер карты в отложенный список
					global $DB;
					$sSql = "
						INSERT INTO 
							p_update_ML_user
							(card_number)
						VALUES
							('".$arUser["UF_DISC"]."')";

					$DB->StartTransaction();

					if($res = $DB->Query($sSql, true))
					{
						$DB->Commit();
					}
					else
					{
						$DB->Rollback();
					}

					return true;
				}
				catch(Exception $e)
				{
					// запишем в журнал событий
					CEventLog::Add(array(
						"SEVERITY" => "WARNING",
						"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
						"MODULE_ID" => "",
						"ITEM_ID" => "Авторизация",
						"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
					));

					// добавляем номер карты в отложенный список
					global $DB;
					$sSql = "
						INSERT INTO 
							p_update_ML_user
							(card_number)
						VALUES
							('".$arUser["UF_DISC"]."')";

					$DB->StartTransaction();

					if($res = $DB->Query($sSql, true))
					{
						$DB->Commit();
					}
					else
					{
						$DB->Rollback();
					}

					return true;
				}

				// преобразуем номер телефона к виду 7xxxxxxxxxx
				$phone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "7$1$2$3$4", $arUser["PERSONAL_PHONE"]);

				$arParameters = array(
					array("Name" => "firstname", "Value" => $arUser["NAME"]),
					array("Name" => "lastname", "Value" => $arUser["LAST_NAME"]),
					array("Name" => "middlename", "Value" => $arUser["SECOND_NAME"]),
					// array("Name" => "emailaddress1", "Value" => $arUser["EMAIL"]),
					array("Name" => "mobilephone", "Value" => $phone),
					array("Name" => "cardnumber", "Value" => $arUser["UF_DISC"]),
					array("Name" => "pl_login", "Value" => $arUser["LOGIN"]),
					// array("Name" => "pl_registrationdate", "Value" => date("Y-m-d")),
					array("Name" => "pl_registration_date", "Value" => date("c")),
					// array("Name" => "shop of activation", "Value" => "LK4lapyru"),
					array("Name" => "shop of activation", "Value" => "Ishop"),
					array("Name" => "ff_shopofactivation", "Value" => "Ishop"),
					// array("Name" => "ff_shopregistration", "Value" => "Ishop"),
				);

				switch ($arUser["PERSONAL_GENDER"]) 
				{
					case 'M':
						$arParameters[] = array("Name" => "gendercode", "Value" => "1");
						break;
					
					case 'F':
						$arParameters[] = array("Name" => "gendercode", "Value" => "2");
						break;
					
					default:
						break;
				}
				
				if((stristr($arFields['EMAIL'], '@register.phone') === false)) $arParameters[] = array("Name" => "emailaddress1", "Value" => $arUser["EMAIL"]);

				if($arUser["PERSONAL_BIRTHDAY"])
				{
					$arParameters[] = array(
						"Name" => "birthdate",
						"Value" => date("c", MakeTimeStamp($arUser["PERSONAL_BIRTHDAY"], "DD.MM.YYYY"))
					);
					$arParameters[] = array(
						"Name" => "ff_isactual",
						"Value" => "1"
					);
				}

				// если емайл и телефон подтвердены - отмечаем, что анкета актуальна и делаем карту бонусной
				if(
					isset($arUser["UF_IS_ACTUAL_EMAIL"])
					and $arUser["UF_IS_ACTUAL_EMAIL"]
					and isset($arUser["UF_IS_ACTUAL_PHONE"])
					and $arUser["UF_IS_ACTUAL_PHONE"]
				)
				{
					$arParameters[] = array("Name" => "haschildrencode", "Value" => 200000);
					$arParameters[] = array("Name" => "ff_shopofactivation", "Value" => "UpdatedByСlient");
					$arParameters[] = array("Name" => "familystatuscode", "Value" => 2);
				}

				$pl_shopsname = "Ishop";

				if(($_SESSION["USER_REGISTER_PAGE"] == "phone") or ($_SESSION["USER_REGISTER_PAGE"] == "phone_tab"))
				{
					if($_SESSION["USER_REGISTER_PAGE"] == "phone_tab")
					{
						$arParameters[] = array("Name" => "ff_shopofactivation", "Value" => "UpdatedByTab");
					} 
					else
					{
						$arParameters[] = array("Name" => "ff_shopofactivation", "Value" => "UpdatedByСassa");
					}

					$arParameters[] = array("Name" => "haschildrencode", "Value" => 200000);

					//если юзера зарегали с кассы - добавляем код магазина
					// получаем из данных пользователя ID магазина, к которому он привязан
					global $USER;
					$rsUser_ = CUser::GetByID($USER->GetID()); 
					$arUser_ = $rsUser_->Fetch();
					$shopID = $arUser_['UF_SHOP'];

					// получаем по ID магазина его код(R***)
					CModule::IncludeModule("iblock");
					$db_props = CIBlockElement::GetProperty((CIBlockTools::GetIBlockId("pet-shops") ?: 0), $shopID, array("sort" => "asc"), Array("CODE"=>"code"));
					$ar_props = $db_props->Fetch();
					$shopCODE = $ar_props['VALUE'];
					$pl_shopsname = $shopCODE;
					//!если юзера зарегали с кассы - добавляем код магазина
				}
				$arParameters[] = array("Name" => "ff_shopregistration", "Value" => $pl_shopsname); 
				
				if($_SESSION["USER_REGISTER_PAGE_2"] == "UpdatedByanketa")
				{
					$arParameters[] = array("Name" => "ff_shopofactivation", "Value" => "UpdatedByanketa");
				}
				// log_(array('Сборный массив данных по юзеру', $arParameters));
				// валидация в ML (ещё раз, на всякий случай)
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "card_validate",
							"parameters" => array(
								array("Name" => "cardnumber", "Value" => $arUser["UF_DISC"]),
							),
						)
					)->ExecuteResult->Value
				);
				// log_(array('Результат повторной валидации', $objXml->GetArray()));
				// если карта уже привязана в ML - ищем id пользователя
				if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == 2)
				{
					// ищем юзера в ML, привязанного к карте
					$objXml->LoadString(
						$client->Execute(
							array(
								"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
								"contractName" => "search_cards_by_number",
								"parameters" => array(
									array("Name" => "cardnumber", "Value" => $arUser["UF_DISC"]),
								),
							)
						)->ExecuteResult->Value
					);
					// log_(array('Вроде как нашелся юзер у карты', $objXml->GetArray()));
					// добавляем id пользователя к параметрам
					// это приведёт к обновлению пользователя в ML, а не созданию нового
					// $arParameters[] = array("Name" => "contactid", "Value" => $objXml->SelectNodes("/Clients/Client/contactid")->textContent());
					$arParameters[] = array("Name" => "contactid", "Value" => $objXml->SelectNodes("/Cards/Card/contactid")->textContent());
				}

				try
				{
					// log_(array('Массив данных юзера перед перед contact_update', $arParameters));
					// создаём/обновляем юзера в ML и привязываем к нему карту
					$objXml->LoadString(
						$client->Execute(
							array(
								"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
								"contractName" => "contact_update",
								"parameters" => $arParameters,
							)
						)->ExecuteResult->Value
					);
					// log_(array('Дергаем contact_update', $objXml->GetArray()));
				}
				catch(Exception $e)
				{
					// запишем в журнал событий
					CEventLog::Add(array(
						"SEVERITY" => "WARNING",
						"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
						"MODULE_ID" => "",
						"ITEM_ID" => "Привязка карты к пользователю",
						"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
					));
				}
				// если обновляем текущего юзера - обновляем информацию по карте
				if($arFields["ID"] == $USER->GetID() and isset($arFields["UF_DISC"]))
				{
					self::SetDataCard($arUser["UF_DISC"]);
				}
			}
		}
	}

	function OnAfterUserAddOrUpdateHandler_old(&$arFields)
	{
		// обновляем информацию по карте
		if (!$arFields['RESULT_MESSAGE']) 
		{
			self::SetDataCard($arFields["UF_DISC"]);
		}

		// добавляем номер карты в отложенный список
		global $DB;
		$num = ($arFields['UF_DISC'])?:$_SESSION["CARD"]["NUMBER"];
		$sSql = "
			INSERT INTO 
				p_update_ML_user
				(card_number)
			VALUES
				('".$num."')";

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

	function OnAfterUserAddOrUpdateHandler(&$arFields)
	{
		return (MANZANA ? self::OnAfterUserAddOrUpdateHandler_ml($arFields) : self::OnAfterUserAddOrUpdateHandler_old($arFields));
	}

	function SearchContactInfo($card)
	{
		return (MANZANA ? self::SearchContactInfo_ml($card) : self::SearchContactInfo_old($card));
	}

	function SearchContactInfo_ml($card)
	{
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			$objXml = new CDataXML();

			try
			{
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5);
				$params = array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
					);
				$client = new SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login" => API_ML_LOGIN,
						"password" => API_ML_PASSWORD,
						"ip" => IP_ADDRESS,
						"innerLogin" => $USER->GetLogin()
					)
				);
			}
			catch(SoapFault $e)
			{
				return self::SearchContactInfo_old($card);
			}
			catch(Exception $e)
			{
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID" => "",
					"ITEM_ID" => "Авторизация",
					"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
				));

				self::$LAST_ERROR = array(
					"code" => $e->detail->details->code,
					"description" => $e->detail->details->description,
					"text" => "Ошибка ML авторизации"
				);
				// return false;
				return self::SearchContactInfo_old($card);
			}

			// валидация карты в ML
			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "card_validate",
						"parameters" => array(
							array("Name" => "cardnumber", "Value" => $card),
						),
					)
				)->ExecuteResult->Value
			);

			// если карта привязана в ML
			if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == 2)
			{
				// ищем юзера, привязанного к карте, получаем его данные
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "search_cards_by_number",
							"parameters" => array(
								array("Name" => "cardnumber", "Value" => $card),
							),
						)
					)->ExecuteResult->Value
				);

				$arData = array(
					"LAST_NAME" => $objXml->SelectNodes("/Cards/Card/lastname")->textContent(),
					"FIRST_NAME" => $objXml->SelectNodes("/Cards/Card/FirstName")->textContent(),
					"SECOND_NAME" => $objXml->SelectNodes("/Cards/Card/MiddleName")->textContent(),
					"BIRTHDAY" => date("d.m.Y", strtotime($objXml->SelectNodes("/Cards/Card/BirthDate")->textContent())),
					"PHONE" => $objXml->SelectNodes("/Cards/Card/mobilephone")->textContent(),
					"EMAIL" => $objXml->SelectNodes("/Cards/Card/emailaddress1")->textContent(),
				);
				return $arData;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	function SearchContactInfo_old($card)
	{
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			global $DB;
			$sSql = "
				SELECT 
					ID,
					number
				FROM 
					p_cards 
				WHERE 
					number='".$card."'";

			if($res = $DB->Query($sSql, true))
			{
				if ($ar_res = $res->Fetch())
				{
					// валидация карты на сайте
					global $USER;
					self::$LAST_ERROR = false;
					$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

					$arUser = CUser::GetList(
						($by="id"),
						($order="desc"),
						array(
							"UF_DISC" => ($card ?: 0),
							"ID" => ($USER->GetID())
						),
						array(
							"SELECT" => array(
								"UF_DISC"
							),
							"NAV_PARAMS" => array(
								"nTopCount" => 1
							)
						)
					)->Fetch();

					if($arUser)
					{
						$arData = array(
							"LAST_NAME" => $arUser['LAST_NAME'],
							"FIRST_NAME" => $arUser['NAME'],
							// "SECOND_NAME" => $arUser,
							"BIRTHDAY" => $arUser['PERSONAL_BIRTHDAY'],
							"PHONE" => $arUser['PERSONAL_PHONE'],
							"EMAIL" => $arUser['EMAIL'],
						);
						return $arData;
					}
				}
			}
		}
		return false;
	}

	function GetCardBalance($card)
	{
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			$objXml = new CDataXML();

			try
			{
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5);
				$params = array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
					);
				$client = new SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login" => API_ML_LOGIN,
						"password" => API_ML_PASSWORD,
						"ip" => IP_ADDRESS,
					)
				);
			}
			catch(SoapFault $e)
			{
				return array("error" => "ML_ERROR", "result" => false, "error" => $e);
			}
			catch(Exception $e)
			{
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID" => "",
					"ITEM_ID" => "Авторизация",
					"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
				));
			}

			// работаем с ML
			if(is_object($objAuthenticate))
			{
				// валидация карты в ML
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "card_validate",
							"parameters" => array(
								array("Name" => "cardnumber", "Value" => $card),
							),
						)
					)->ExecuteResult->Value
				);

				// если карта существует в ML
				if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() != 1)
				{
					// если карта привязана к пользователю в ML
					if($objXml->SelectNodes("/cardvalidateresult/validationresultcode")->textContent() == 2)
					{
						// получаем данные юзера
						$objXml->LoadString(
							$client->Execute(
								array(
									"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
									"contractName" => "search_cards_by_number",
									"parameters" => array(
										array("Name" => "cardnumber", "Value" => $card),
									),
								)
							)->ExecuteResult->Value
						);
						// echo "<pre>";print_r($objXml->SelectNodes("/Cards/Card"));echo "</pre>";

						// получаем данные карты
						$objXml->LoadString(
							$client->Execute(
								array(
									"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
									"contractName" => "cards",
									"parameters" => array(
										// array("Name" => "contact_id", "Value" => $objXml->SelectNodes("/Clients/Client/contactid")->textContent()),
										array("Name" => "contact_id", "Value" => $objXml->SelectNodes("/Cards/Card/contactid")->textContent()),
									),
								)
							)->ExecuteResult->Value
						);

						if($objXml->SelectNodes("/Cards/Card"))
						{
							// сохраняем данные карты в сессию
							$_SESSION["CARD"] = array(
								"ID" => $objXml->SelectNodes("/Cards/Card/pl_bonuscardid")->textContent(),
								"TYPE" => $objXml->SelectNodes("/Cards/Card/pl_bonustype")->textContent(),
								"TYPE_TEXT" => $objXml->SelectNodes("/Cards/Card/pl_bonustype_text")->textContent(),
								"NUMBER" => $objXml->SelectNodes("/Cards/Card/pl_number")->textContent(),
								"STATUS" => $objXml->SelectNodes("/Cards/Card/pl_status_text")->textContent(),
								"BALANCE" => $objXml->SelectNodes("/Cards/Card/pl_active_balance")->textContent(),
								"DISCOUNT" => intval($objXml->SelectNodes("/Cards/Card/pl_discount")->textContent()),
								"SUMM" => $objXml->SelectNodes("/Cards/Card/pl_summdiscounted")->textContent(),
								"CREDIT" => $objXml->SelectNodes("/Cards/Card/pl_credit")->textContent(),
								"DEBET" => $objXml->SelectNodes("/Cards/Card/pl_debet")->textContent(),
							);

							// исправляем тип карты
							// товарищи из манзаны гарантируют: не нулевой pl_debet означает, что карта бонусная
							if($objXml->SelectNodes("/Cards/Card/pl_debet")->textContent() > 0)
							{
								$_SESSION["CARD"]["TYPE"] = 1;
								$_SESSION["CARD"]["TYPE_TEXT"] = "Bonus";
							}
						}

						// товарищи из манзаны гарантируют: не нулевой pl_debet <=> карта бонусная
						$arResult["IS_BONUS_CARD"] = (($objXml->SelectNodes("/Cards/Card/pl_debet")->textContent() > 0) ? "Y" : "N");
						$arResult["DEBET"] = ($objXml->SelectNodes("/Cards/Card/pl_active_balance")->textContent());

						// return $arResult["DEBET"];
						return array("cnt_bonus" => $arResult["DEBET"], "sale_amount" => intval($objXml->SelectNodes("/Cards/Card/pl_discount")->textContent()), "result" => true);
					}
				}
				else
				{
					return "card not found";
					return array("error" => "card not found", "result" => false);
				}
			}
			else
			{
				// return "ML_ERROR";
				return array("error" => "ML_ERROR", "result" => false);
			}
		}
		return false;
	}

	function GetCardBalanceLocal($card, $userID = 0)
	{
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			$arResult["CARD"] = ($card)?:(self::GetDataCard() ?: array());

			// если карта бонусная, то получаем внутренний счёт пользователя
			// if(!empty($arResult["CARD"]) and ($arResult["CARD"]["TYPE"] == 1))
			// {
				CModule::IncludeModule("sale");
				$arResult["SALE_USER_ACCOUNT"] = CSaleUserAccount::GetByUserID(($userID)?:$USER->GetID(), "RUB");
				return $arResult["SALE_USER_ACCOUNT"]["CURRENT_BUDGET"];
			// }
		}
		return false;
	}

	function GetCardInfoLocal($card)
	{
		global $USER;
		self::$LAST_ERROR = false;
		$card = filter_var($card, FILTER_SANITIZE_NUMBER_INT);

		if($card)
		{
			$arResult["CARD"] = (self::GetDataCard() ?: false);

			return $arResult["CARD"];
		}
		return false;
	}

	/**
	 * @param $phone integer phone number
	 * @return int phone number if it has in ML
	 */
	public static function CheckUserPhone($phone){
		global $USER;
		self::$LAST_ERROR = false;
		$phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
		if($phone) {
			$objXml = new CDataXML();
			try {
				// создаём soap-клиент и авторизуемся под админом
				ini_set('default_socket_timeout', 5);
				$params = array(
					'trace'              => 1,
					'exceptions'         => 1,
					"connection_timeout" => 5
				);
				$client = new SoapClient(API_ML_WSDL, $params);

				$objAuthenticate = $client->Authenticate(
					array(
						"login"      => API_ML_LOGIN,
						"password"   => API_ML_PASSWORD,
						"ip"         => IP_ADDRESS,
						"innerLogin" => $USER->GetLogin()
					)
				);
			} catch (SoapFault $e) {

			} catch (Exception $e) {
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY"      => "WARNING",
					"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
					"MODULE_ID"     => "",
					"ITEM_ID"       => "Авторизация",
					"DESCRIPTION"   => "[" . $e->detail->details->code . "] " . $e->detail->details->description,
				));

				self::$LAST_ERROR = array(
					"code"        => $e->detail->details->code,
					"description" => $e->detail->details->description,
					"text"        => "Ошибка ML авторизации"
				);
			}

			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "client_search",
						"parameters" => array(
							array("Name" => "maxresultsnumber", "Value" => "1"),
							array("Name" => "mobilephone", "Value" => $phone),
						),
					)
				)->ExecuteResult->Value
			);

			$res = null;
			if($objXml->SelectNodes("/Clients/Client/mobilephone")){
				$res = $objXml->SelectNodes("/Clients/Client/mobilephone")->textContent();
			}
			// валидация карты в ML
			return $res;
		}
	}

	function GetVBC($arFields)
	{
		global $USER;
		$arUser = $USER->GetByID($arFields['user_fields']["ID"])->Fetch();

		// $arUser["PERSONAL_PHONE"] = 9065555111;

		$objXml = new CDataXML();
		try
		{
			// создаём soap-клиент и авторизуемся под админом
			ini_set('default_socket_timeout', 5);
			$params = array(
				'trace' => 1,
				'exceptions'=> 1,
				"connection_timeout" => 5
				);
			$client = new SoapClient(API_ML_WSDL, $params);

			$objAuthenticate = $client->Authenticate(
				array(
					"login" => API_ML_LOGIN,
					"password" => API_ML_PASSWORD,
					"ip" => (IP_ADDRESS)?:'127.0.0.1',
					"innerLogin" => $USER->GetLogin()
				)
			);
		}
		catch(SoapFault $e){}
		catch(Exception $e){}

		// преобразуем номер телефона к виду 7xxxxxxxxxx
		$phone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "7$1$2$3$4", (($arFields['PHONE'])?:$arUser["PERSONAL_PHONE"]));

		$objXml->LoadString(
			$client->Execute(
				array(
					"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
					"contractName" => "client_search",
					"parameters" => array(
						array("Name" => "maxresultsnumber", "Value" => "1"),
						array("Name" => "mobilephone", "Value" => $phone),
					),
				)
			)->ExecuteResult->Value
		);

		//нашли юзера с таким номером?
		$userExist = $objXml->SelectNodes("/Clients/Client/mobilephone");
		$userCards = array();

		if($userExist){
			foreach ($objXml->SelectNodes("/Clients/Client")->children() as $key_user_params => $value_user_params){
				if ($value_user_params->name == 'Card') {
					foreach ($value_user_params->children() as $key___ => $value___) {
						if ($value___->name == 'CardNumber') {
							$userCards[] = $value___->content;
						}
					}
				}
			}
		}

		if($userExist and (count($userCards) == 1)){
			// echo "<pre>";print_r('Нашли юзера в МЛ по номеру телефона');echo "</pre>"."\r\n";
			//если мы подтягиваем юзеру карту из МЛ по номеру телефона - то не апдейтим данные в МЛ
			$_SESSION['disableMlUpdate'] = true;
			
			// если тут пришла БК и данные из МЛ отличаются от сайтовских то данные на сайте обновить
			// делаю тут т.к. в этом месте уже есть данные из МЛ что бы повторно не делать запрос потом
			if (  substr( $userCards[0], 0, 2 ) != '27' ) { // 26 - бк, 27 - вбк
				
				// данные из МЛ по пользователю
				$userDate = array(
					'NAME' => $objXml->SelectNodes("/Clients/Client/firstname")->content, // имя
					'LAST_NAME' => $objXml->SelectNodes("/Clients/Client/lastname")->content, // фамилия
					'SECOND_NAME' => $objXml->SelectNodes("/Clients/Client/middlename")->content, // отчество
				);
				
				$updateDataUser = array(); // массив с полями которые нужно обновлять
				
				// если данные есть в ML и они отличаются от тех что на сайте то надо будет обновить на сайте
				if ( ($userDate['NAME'] != '') && ($arUser['NAME'] != $userDate['NAME'] ) ) $updateDataUser['NAME'] = $userDate['NAME'];
				if ( ($userDate['LAST_NAME'] != '') && ($arUser['LAST_NAME'] != $userDate['LAST_NAME'] ) ) $updateDataUser['LAST_NAME'] = $userDate['LAST_NAME'];
				if ( ($userDate['SECOND_NAME'] != '') && ($arUser['SECOND_NAME'] != $userDate['SECOND_NAME'] ) ) $updateDataUser['SECOND_NAME'] = $userDate['SECOND_NAME'];
				
				// если есть что обновить обновляем пользовательские данные
				if (!empty($updateDataUser)) {
					$user = new CUser;
					$user->Update($arUser['ID'], $updateDataUser);
				}
				
			}
			////
			
			return $objXml->SelectNodes("/Clients/Client/Card/CardNumber")->content;
		}else{
			//если юзера с таким номером нет - пытаемся получить ВБК и привязать к юзеру
			// echo "<pre>";print_r('НЕ нашли юзера в МЛ по номеру телефона');echo "</pre>"."\r\n";

			$arParameters = array(
				array("Name" => "firstname", "Value" => $arUser["NAME"]),
				array("Name" => "lastname", "Value" => $arUser["LAST_NAME"]),
				array("Name" => "middlename", "Value" => $arUser["SECOND_NAME"]),
				array("Name" => "mobilephone", "Value" => $phone),
				// array("Name" => "cardnumber", "Value" => $arUser["UF_DISC"]),
				array("Name" => "pl_login", "Value" => $arUser["LOGIN"]),
				array("Name" => "pl_registration_date", "Value" => date("c")),
				array("Name" => "shop of activation", "Value" => "Ishop"),
				array("Name" => "ff_shopofactivation", "Value" => "Ishop"),
				array("Name" => "ff_shopregistration", "Value" => "Ishop"),
			);

			switch ($arUser["PERSONAL_GENDER"]) 
			{
				case 'M':
					$arParameters[] = array("Name" => "gendercode", "Value" => "1");
					break;
				
				case 'F':
					$arParameters[] = array("Name" => "gendercode", "Value" => "2");
					break;
				
				default:
					break;
			}
			
			if((stristr($arFields['EMAIL'], '@register.phone') === false)) $arParameters[] = array("Name" => "emailaddress1", "Value" => $arUser["EMAIL"]);

			if($arUser["PERSONAL_BIRTHDAY"]){
				$arParameters[] = array(
					"Name" => "birthdate",
					"Value" => date("c", MakeTimeStamp($arUser["PERSONAL_BIRTHDAY"], "DD.MM.YYYY"))
				);
				$arParameters[] = array(
					"Name" => "ff_isactual",
					"Value" => "1"
				);
			}

			// если емайл и телефон подтвердены - отмечаем, что анкета актуальна и делаем карту бонусной
			if(
				isset($arUser["UF_IS_ACTUAL_EMAIL"])
				and $arUser["UF_IS_ACTUAL_EMAIL"]
				and isset($arUser["UF_IS_ACTUAL_PHONE"])
				and $arUser["UF_IS_ACTUAL_PHONE"]
			)
			{
				$arParameters[] = array("Name" => "haschildrencode", "Value" => 200000);
				$arParameters[] = array("Name" => "ff_shopofactivation", "Value" => "UpdatedByСlient");
				$arParameters[] = array("Name" => "familystatuscode", "Value" => 2);
			}

			try
			{
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "contact_update",
							"parameters" => $arParameters,
						)
					)->ExecuteResult->Value
				);
			}
			catch(Exception $e){}

			$objXml->LoadString(
				$client->Execute(
					array(
						"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
						"contractName" => "client_search",
						"parameters" => array(
							array("Name" => "maxresultsnumber", "Value" => "1"),
							// array("Name" => "contact_id", "Value" => $contactID),
							array("Name" => "mobilephone", "Value" => $phone),
						),
					)
				)->ExecuteResult->Value
			);

			return $objXml->SelectNodes("/Clients/Client/Card/CardNumber")->content;
		}


		// log_($arParameters);
		// log_($objXml);
		// log_($objXml->GetArray());
		// если обновляем текущего юзера - обновляем информацию по карте
		// if($arFields["ID"] == $USER->GetID() and isset($arFields["UF_DISC"]))
		// {
		// 	self::SetDataCard($arUser["UF_DISC"]);
		// }
	}

	function setUserPets($arFields, $method)
	{
		$userId = 0;
		$arUser = array();
		if(is_array($arFields['PROPERTY_VALUES']) && array_key_exists('USER_ID', $arFields['PROPERTY_VALUES'])) {
			$userId = $arFields['PROPERTY_VALUES']['USER_ID'];
		} else {
			// сохранение из админки
			$arProp = CIBlockProperty::GetList(array(), array(
				'IBLOCK_ID' => $arFields['IBLOCK_ID'],
				'CHECK_PERMISSIONS' => 'N',
				'USER_TYPE' => 'UserID',
				'CODE' => 'USER_ID'
			))->Fetch();

			if ($arProp['ID'] && is_array($arFields['PROPERTY_VALUES'][$arProp['ID']]) && !empty($arFields['PROPERTY_VALUES'][$arProp['ID']])) {
				$arPropValue = reset($arFields['PROPERTY_VALUES'][$arProp['ID']]);
				$userId = $arPropValue['VALUE'];
			}
		}
		if ($userId > 0) {
			$arUser = CUser::GetByID($userId)->Fetch();
		}

		if($arUser['UF_DISC'])
		{
			try {
				ini_set('default_socket_timeout', 5);

				$objXml = new CDataXML();

				$oSoapClient = new \SoapClient(API_ML_WSDL, array(
					'trace' => 1,
					'exceptions'=> 1,
					'connection_timeout' => 5
				));

				$sessionId = $oSoapClient->Authenticate(array(
					'login' => API_ML_LOGIN,
					'password' => API_ML_PASSWORD,
					'ip' => IP_ADDRESS,
					'innerLogin' => 'mob_api',
				))->AuthenticateResult->SessionId;
			} catch (Exception $e) {
			}

			if ($sessionId) {
				$arParameters = array(
					array('Name' => 'cardnumber', 'Value' => $arUser['UF_DISC'])
				);

				$objXml->LoadString(
					$oSoapClient->Execute(
						array(
							'sessionId' => $sessionId,
							'contractName' => 'card_validate',
							'parameters' => $arParameters
						)
					)->ExecuteResult->Value
				);

				if($objXml->SelectNodes('/cardvalidateresult/validationresultcode')->textContent() == 2)
				{
					// ищем юзера в ML, привязанного к карте
					$objXml->LoadString(
						$oSoapClient->Execute(
							array(
								'sessionId' => $sessionId,
								'contractName' => 'search_cards_by_number',
								'parameters' => $arParameters
							)
						)->ExecuteResult->Value
					);

					// добавляем id пользователя к параметрам
					// это приведёт к обновлению пользователя в ML, а не созданию нового
					$arParameters[] = array('Name' => 'contactid', 'Value' => $objXml->SelectNodes('/Cards/Card/contactid')->textContent());
				}

				$oCategoryes = CIBlockSection::GetList(
					array(
						'SORT' => 'ASC'
					),
					array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('kinds'),
						'ACTIVE' => 'Y',
						'SECTION_ID' => false,
					),
					false,
					array(
						'ID',
						'CODE'
					)
				);

				$arCategories = array();
				while ($arCategory = $oCategoryes->Fetch())
				{
					$arCategories[$arCategory['ID']] = $arCategory['CODE'];
					if ($arCategory['CODE'] == 'ff_others') {
						$arParameters[$arCategory['CODE']] = array('Name' => $arCategory['CODE'], 'Value' => '');
					} else {
						$arParameters[$arCategory['CODE']] = array('Name' => $arCategory['CODE'], 'Value' => 0);
					}
				}

				$arFilter = array(
					'IBLOCK_ID' => $arFields['IBLOCK_ID'],
					'ACTIVE' => 'Y',
					'PROPERTY_USER_ID' => $arUser['ID']
				);

				if ($method == 'delete') {
					$arFilter['!=ID'] = $arFields['ID'];
				}

				$oPets = CIBlockElement::GetList(
					array(
						'ID' => 'ASC'
					),
					$arFilter,
					false,
					false,
					array(
						'PROPERTY_PET_CATEGORY',
						'PROPERTY_PET_BREED.NAME',
						'PROPERTY_PET_BREED_OTHER'
					)
				);

				while ($arPet = $oPets->Fetch())
				{
					$categoryCode = $arCategories[$arPet['PROPERTY_PET_CATEGORY_VALUE']];
					if ($categoryCode == 'ff_others') {
						$arParameters['ff_others']['Value'] .= ($arParameters['ff_others']['Value'] ? ' и ' : '') . ($arPet['PROPERTY_PET_BREED_OTHER_VALUE'] ? $arPet['PROPERTY_PET_BREED_OTHER_VALUE'] : $arPet['PROPERTY_PET_BREED_NAME']);
					} else {
						$arParameters[$categoryCode]['Value'] = 1;
					}
				}
				$arParameters = array_values($arParameters);

				try
				{
					$objXml->LoadString(
						$oSoapClient->Execute(
							array(
								'sessionId' => $sessionId,
								'contractName' => 'contact_update',
								'parameters' => $arParameters,
							)
						)->ExecuteResult->Value
					);
				}
				catch(Exception $e)
				{
				}
			}
		}
	}

	function GetCardByNumber($phone)
	{
		$objXml = new CDataXML();
		try
		{
			// создаём soap-клиент и авторизуемся под админом
			ini_set('default_socket_timeout', 5);
			$params = array(
				'trace' => 1,
				'exceptions'=> 1,
				"connection_timeout" => 5
				);
			$client = new SoapClient(API_ML_WSDL, $params);

			$objAuthenticate = $client->Authenticate(
				array(
					"login" => API_ML_LOGIN,
					"password" => API_ML_PASSWORD,
					"ip" => IP_ADDRESS,
					"innerLogin" => 'cardHistory'
				)
			);
		}
		catch(SoapFault $e){}
		catch(Exception $e){}

		// преобразуем номер телефона к виду 7xxxxxxxxxx
		$phone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "7$1$2$3$4", $phone);

		$objXml->LoadString(
			$client->Execute(
				array(
					"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
					"contractName" => "client_search",
					"parameters" => array(
						array("Name" => "maxresultsnumber", "Value" => "1"),
						array("Name" => "mobilephone", "Value" => $phone),
					),
				)
			)->ExecuteResult->Value
		);

		//нашли юзера с таким номером?
		$userExist = $objXml->SelectNodes("/Clients/Client/mobilephone");
		$userCards = array();

		if($userExist){
			foreach ($objXml->SelectNodes("/Clients/Client")->children() as $key_user_params => $value_user_params){
				if ($value_user_params->name == 'Card') {
					foreach ($value_user_params->children() as $key___ => $value___) {
						if ($value___->name == 'CardNumber') {
							$userCards[] = $value___->content;
						}
					}
				}
			}
		}

		if($userExist and (count($userCards) == 1)){
			return $objXml->SelectNodes("/Clients/Client/Card/CardNumber")->content;
		}
	}

	// создаём soap-клиент 
	function CreateClient()
	{
		return new \SoapClient(API_ML_WSDL);
	}
	////

	// функция - приконектится к манзане как админ
	// вх: 
	//		$client
	// вых: если всё хорошо вернёт objAuthenticate - можно использовать для запросов 
	// 		!!!если неудачно то вернёт NULL насколько я понял
	function ConnectAdmin($client)
	{
		try
		{
			$objAuthenticate = $client->Authenticate(
				array(
					"login" => API_ML_LOGIN,
					"password" => API_ML_PASSWORD,
					"ip" => IP_ADDRESS,
					// "innerLogin" => $USER->GetLogin()
				)
			);
		}
		catch(Exception $e)
		{
			// запишем в журнал событий
			// CEventLog::Add(array(
			// 	"SEVERITY" => "WARNING",
			// 	"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
			// 	"MODULE_ID" => "",
			// 	"ITEM_ID" => "Авторизация",
			// 	"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
			// ));
		}

		return $objAuthenticate;
	}
	////

	// функция - поиск по клиентам
	// вх:	
	// 		property 		 - массив со значениями property_code = property_value
	//			property_code	 - название поля по каому искать // потом можно расширить до массива
	// 			property_value	 - значение параметра
	// 		objXml			 - обьект CDataXML
	//		objAuthenticate	 - авторизация под админом например
	//		client
	// вых: 
	// 		$objXml			 - обьект CDataXML
	function ClientSersh($objXml, $client, $objAuthenticate, $property)
	{
		return self::CardGetList($objXml, $client, $objAuthenticate, "client_search", self::GetArrayForGetList($property) );
	}
	//// 

	// search_cards_by_number
	function SearchCardsByNumber($objXml, $client, $objAuthenticate, $property)
	{
		return self::CardGetList($objXml, $client, $objAuthenticate, "search_cards_by_number", self::GetArrayForGetList($property) );
	}
	////

	//  card_validate - узнать например id карты
	function CardValidate($objXml, $client, $objAuthenticate, $property)
	{
		return self::CardGetList($objXml, $client, $objAuthenticate, "card_validate", self::GetArrayForGetList($property) );
	}
	////	

	//  contact_card_update - обновить карту виртуальную на физическую например		
	function ContactCardUpdate($objXml, $client, $objAuthenticate, $property)
	{
		return self::CardGetList($objXml, $client, $objAuthenticate, "contact_card_update", self::GetArrayForGetList($property) );
	}
	////

	//  contact_card_update - обновить карту виртуальную на физическую например		
	function ContactUpdate($objXml, $client, $objAuthenticate, $property)
	{
		return self::CardGetList($objXml, $client, $objAuthenticate, "contact_update", self::GetArrayForGetList($property) );
	}
	////

	// функция соберёт массив для поискового запроса в манзану
	// вх:
	//		property 		- массив со значениями property_code = property_value
	// 			property_code 	- массив с параметрами
	// 			property_value 	- массив с значениями
	// вых: массив parameters
	function GetArrayForGetList($property)
	{
		$result = array();

		foreach ($property as $key => $value) {
			$result[] = array(
				"Name" => $key,
				"Value" => $value
			);
		}

		return $result;
	}
	////

	// функция 
	// вх:	
	//		contractName	- имя функции
	//		parameters		- параметры для запроса
	//		objAuthenticate	- авторизация под админом например
	//		objXml			- обьект CDataXML
	//		$client
	// вых: 
	// 		objXml - если успех обьект CDataXML
	function CardGetList($objXml, $client, $objAuthenticate, $contractName, $parameters )
	{
		// $result = false;
		$objXml->LoadString(
			$client->Execute(
				array(
					"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
					"contractName" => $contractName, 
					"parameters" => $parameters
				)
			)->ExecuteResult->Value
		);

		return $objXml;
	}
	////

	// надо добавить функции для получения данных их результатов


}
?>