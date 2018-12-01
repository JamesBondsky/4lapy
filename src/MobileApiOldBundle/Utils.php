<?

namespace FourPaws\MobileApiOldBundle;

class Utils
{
	public static $LAST_ERROR = false;

	// SEO обработка текста
	static function SeoWordProcessing($seoText, $arData)
	{
		// обработка {sectionN:}...{/sectionN}
		while(preg_match("/\{section(?P<dl>\d{1}):\}((?!.*\{section\k<dl>:\}).*)\{\/section\k<dl>\}/s", $seoText, $arPregMatch))
		{
			list($fullText, $depthLevel, $text) = $arPregMatch;

			if(isset($arData["section"][$depthLevel]) and !isset($arData["section"][$depthLevel + 1]))
			{
				$seoText = str_replace($fullText, $text, $seoText);
			}
			else
			{
				$seoText = str_replace($fullText, "", $seoText);
			}
		}

		// обработка {=sectionN.XXX}, {=element.XXX}
		while(preg_match("/\{=(|lower|upper)(?:|\s)(section|element)(\d{0,1})\.(name)\}/s", $seoText, $arPregMatch))
		{
			list($fullText, $transform, $name, $depthLevel, $property) = $arPregMatch;

			$textReplace = "";
			$depthLevel = ($depthLevel ?: 0);

			if(isset($arData[$name][$depthLevel][$property]))
			{
				switch($transform)
				{
					case "lower":
						$textReplace = strtolower($arData[$name][$depthLevel][$property]);
						break;

					case "upper":
						$textReplace = strtoupper($arData[$name][$depthLevel][$property]);
						break;

					default:
						$textReplace = $arData[$name][$depthLevel][$property];
						break;
				}
			}

			$seoText = str_replace($fullText, $textReplace, $seoText);
		}

		// обработка {=random(...|...|...)}
		while(preg_match("/\{=(|lower|upper)(?:|\s)random\(((?!.*\{=).*)\)\}/s", $seoText, $arPregMatch))
		{
			list($fullText, $transform, $randText) = $arPregMatch;

			$arRandText = explode("|", $randText);

			$textReplace = $arRandText[array_rand($arRandText, 1)];

			switch($transform)
			{
				case "lower":
					$textReplace = strtolower($textReplace);
					break;

				case "upper":
					$textReplace = strtoupper($textReplace);
					break;

				default:
					break;
			}

			$seoText = str_replace($fullText, $textReplace, $seoText);
		}

		return $seoText;
	}

	// отправка смс
	static function SendSMS_old($toPhone, $message) // отправляет только смс через smstraffic.ru 
	{
		// log_sms($toPhone.';'.$message);
		// return true;
		static::$LAST_ERROR = false;
		$result = false;

		// удаляем нецифровые символы
		$toPhone = preg_replace("/\D/", "", $toPhone);

		// добавляем 7 в начало номера
		$toPhone = ((strlen($toPhone) == 10) ? "7" : "").$toPhone;

		if(strlen($toPhone) != 11)
		{
			self::$LAST_ERROR = array(
				"code" => "",
				"description" => "not valid",
				"text" => ""
			);
		}
		else
		{
			$objXml = new CDataXML();
			$arResponse = array();
			$logID_SMS_connect = start_log('SMS', 'SMS_connect');
			try
			{
				// создаём soap-клиент
				ini_set('default_socket_timeout', 5);
				$params = array("trace" => true, "connection_timeout" => 5);
				$client = new SoapClient(API_SMS_TRAFFIC_WSDL, $params);
			}
			catch(Exception $e)
			{
				try
				{
					ini_set('default_socket_timeout', 5);
					$params = array("trace" => true, "connection_timeout" => 5);
					$client = new SoapClient(API_SMS_TRAFFIC_WSDL_2, $params);
				}
				catch(Exception $e)
				{
					log_sms($toPhone.';'.$message);
					return true;
				}
				catch(SoapFault $e)
				{
					log_sms($toPhone.';'.$message);
					return true;
				}
				// запишем в журнал событий
				CEventLog::Add(array(
					"SEVERITY" => "WARNING",
					"AUDIT_TYPE_ID" => "SMS_TRAFFIC",
					"MODULE_ID" => "",
					"ITEM_ID" => "Подключение к SOAP",
					"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
				));
			}
			catch(SoapFault $e)
			{
				try
				{
					ini_set('default_socket_timeout', 5);
					$params = array("trace" => true, "connection_timeout" => 5);
					$client = new SoapClient(API_SMS_TRAFFIC_WSDL_2, $params);
				}
				catch(Exception $e)
				{
					log_sms($toPhone.';'.$message);
					return true;
				}
				catch(SoapFault $e)
				{
					log_sms($toPhone.';'.$message);
					return true;
				}
			}
			end_log($logID_SMS_connect);
			$logID_SMS_work = start_log('SMS', 'SMS_work');
			if(is_object($client))
			{
				$objXml->LoadString(
					$client->send(
						API_SMS_TRAFFIC_LOGIN,
						API_SMS_TRAFFIC_PASSWORD,
						$toPhone,
						$message,
						null,
						1,
						0,
						null,
						5 
					)
				);

				foreach($objXml->SelectNodes("/reply")->children() as $value)
				{
					$arResponse[$value->name] = $value->content;
				}

				if($arResponse["result"] == "OK")
				{
					$result = true;
				}
				else
				{
					self::$LAST_ERROR = array(
						"code" => $arResponse["code"],
						"description" => $arResponse["description"],
						"text" => ""
					);
				}
			}

		}
		end_log($logID_SMS_work);
		return $result;
	}

	function SendImmediateSMS($toPhone, $message) // тут тоже что и в SendSMS только смс сразу шлёт
	{
		$result = false;

		//корректируем номер телефона
		// удаляем нецифровые символы
		$toPhone = preg_replace("/\D/", "", $toPhone);
		// добавляем 7 в начало номера
		$toPhone = ((strlen($toPhone) == 10) ? "7" : "").$toPhone;
		////

		if( (strlen($toPhone) == 11) && ($message != '') ) // если есть все данные
		{ 
			$param = array(
				'login' => API_SMS_TRAFFIC_LOGIN,
				'password' => API_SMS_TRAFFIC_PASSWORD,
				'phones' => $toPhone,
				'message' => $message,
				'originator' => '4lapy', // что будет в отправителе в смс
				'rus' => '1', // что бы Русские буквы приходили // 0 - что бы в кирилицу преобразовывалась
				// 'route' => 'Sms(360)-viber', // путь // сначало в вайбер, если в течение 90 с. не пришло то отправить в смс
				// 'route' => 'sms', // путь // сначало в вайбер, если в течение 90 с. не пришло то отправить в смс
				'route' => 'sms', // путь // сначало в вайбер, если в течение 90 с. не пришло то отправить в смс
			);

			// отправляем запрос на отправку смс // post
			$host = 'http://sds.intervale.ru/smartdelivery-in/multi.php';
			$myCurl = curl_init();
			curl_setopt_array($myCurl, array(
			    CURLOPT_URL => $host,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POST => true,
			    CURLOPT_POSTFIELDS => http_build_query( $param )
			));
			$response = curl_exec($myCurl);
			$stat = 'function sent';
			if (strpos($response, '<result>OK</result>') !== false){
				$stat = 'function sent -ok';
				$result = true; // если пришло ОК то сообщение отправилось
			}else{
				//тут нада сигнализировать об ошибке, чтобы монитор сомг подхватить
				file_put_contents( $_SERVER["DOCUMENT_ROOT"].'/in/smsErrors/smsError_'.date('d.m.Y H:i:s').'.txt', date('d.m.Y H:i:s').' '.$toPhone.' '.$message.' '.$response."\n",FILE_APPEND );
			}

			file_put_contents( $_SERVER["DOCUMENT_ROOT"].'/test/log_send_sms.txt', date('d.m.Y H:i:s').' '.$toPhone.' '.$message.' '.$stat."\n",FILE_APPEND );

			curl_close($myCurl);
			////
		} 

		return $result;
	}

	function SendSMS($toPhone, $message) // отправляем через smstraffic.ru но не wsdl а post
	{	//(так они хотели что бы отправлять в вайбер а если нет то смс (сама smstraffic.ru будут решать  куда отправлять) )
	
		$result = false;

		//корректируем номер телефона
		// удаляем нецифровые символы
		$toPhone = preg_replace("/\D/", "", $toPhone);
		// добавляем 7 в начало номера
		$toPhone = ((strlen($toPhone) == 10) ? "7" : "").$toPhone;
		////

		if( (strlen($toPhone) == 11) && ($message != '') ) // если есть все данные
		{ 
			$param = array(
				'login' => API_SMS_TRAFFIC_LOGIN,
				'password' => API_SMS_TRAFFIC_PASSWORD,
				'phones' => $toPhone,
				'message' => $message,
				'originator' => '4lapy', // что будет в отправителе в смс
				'rus' => '1', // что бы Русские буквы приходили // 0 - что бы в кирилицу преобразовывалась
				'route' => 'viber(90)-sms', // путь // сначало в вайбер, если в течение 90 с. не пришло то отправить в смс
			);

			// отправляем запрос на отправку смс // post
			$host = 'http://sds.intervale.ru/smartdelivery-in/multi.php';
			$myCurl = curl_init();
			curl_setopt_array($myCurl, array(
			    CURLOPT_URL => $host,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POST => true,
			    CURLOPT_POSTFIELDS => http_build_query( $param )
			));
			$response = curl_exec($myCurl);
			$stat = 'function sent';
			if (strpos($response, '<result>OK</result>') !== false){
				$stat = 'function sent -ok';
				$result = true; // если пришло ОК то сообщение отправилось
			}else{
				//тут нада сигнализировать об ошибке, чтобы монитор сомг подхватить
				file_put_contents( $_SERVER["DOCUMENT_ROOT"].'/in/smsErrors/smsError_'.date('d.m.Y H:i:s').'.txt', date('d.m.Y H:i:s').' '.$toPhone.' '.$message.' '.$response."\n",FILE_APPEND );
			}

			file_put_contents( $_SERVER["DOCUMENT_ROOT"].'/test/log_send_sms.txt', date('d.m.Y H:i:s').' '.$toPhone.' '.$message.' '.$stat."\n",FILE_APPEND );

			curl_close($myCurl);
			////
		} 

		return $result;
	}

	// отправка письма-подтверждения изменения email
	static function SendMessageEditEmail($new_email, $emailOld)
	{
		$email = trim($new_email);
		$emailOld = trim($emailOld);

		if(filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$_SESSION["EDIT_EMAIL"]["REQUEST"]["email"] = $email;
			$_SESSION["EDIT_EMAIL"]["SEND_CODE_EMAIL"] = md5($email.$emailOld);

			// в ссылке, по которой пользователь переходит из письма, будут указаны емайл и верификационный код
			CEvent::SendImmediate(
				"SEND_VERIFICATION_CODE_EDIT_EMAIL",
				"s1",
				array(
					"VERIFICATION_CODE" => $_SESSION["EDIT_EMAIL"]["SEND_CODE_EMAIL"],
					// "EMAIL_TO" => $email,
					"EMAIL_TO" => $emailOld,
					"EMAIL_NEW" => $email,
					"AUTH_HASH" => fUserForcedAuthorization(),
					"YEAR" => date("Y", time()),
				)
			);

			$result["result"] = true;

			return $result;
		}
	}

	static function VerificationEmail($new_email, $code)
	{
		if($_SESSION["EDIT_EMAIL"]["SEND_CODE_EMAIL"] == $_REQUEST["code"])
		{
			$_SESSION["EDIT_EMAIL"]["IS_ACTUAL_EMAIL"] = "Y";
			$result["result"] = true;
			return $result;
		}
	}

	static function OnBeforeSendSMSHandler($phone, $text)
	{
		// здесь внедряйте свое решение по отправке СМС
		// номер телефона для отправки - переменная $phone
		// текст СМС - переменная $text
		// для того, что бы модуль не пытался отправить СМС,
		// нужно, что бы ваш обработчик вернул false
		self::SendImmediateSMS($phone, $text);

		return false;
	}

	static function TrySendDeliverySms($phone, $message)
	{
		if (date("G") >= '9' and date("G") < '21')
		{
			$resultSMS["result"] = self::SendSMS($phone,$message);
		}
		elseif (date("G") >= '21' or date("G") < '9')
		{
			\Bitrix\Main\Loader::includeModule('iblock');
			$oElement = new \CIBlockElement();

			$sectionID = '10321';

			$oElement->Add(array(
				'IBLOCK_SECTION_ID' => $sectionID,
				'IBLOCK_ID' => \CIBlockTools::GetIBlockId('send_delivered_sms'),
				'NAME' => $phone,
				'ACTIVE' => 'Y',
				'PREVIEW_TEXT' => html_entity_decode($message),
			), false, false, false);
		}
	}

	static function PreparePhone($phone)
	{
		\Bitrix\Main\Loader::includeModule('bxmod.auth');
		return BxmodAuth::CheckPhone($phone);
	}
}
?>