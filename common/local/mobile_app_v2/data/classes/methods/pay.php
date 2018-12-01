<?

class pay extends APIServer
{
	const PAYMENT_TYPE = array('cashless', 'applepay', 'android', 'cash');

	function toWIN($str) 
	{
		if (SITE_CHARSET == "windows-1251" or SITE_CHARSET == "WINDOWS-1251" or SITE_CHARSET == "cp1251" or SITE_CHARSET == "CP1251")
		{
			return iconv("utf-8", "windows-1251", $str);
		}
		else
		{
			return $str;
		}
	}

	public function post($arInput)
	{
		$arResult = array();
		$OrderNumber = $arInput['order_id'];
		$pay_type = $arInput['payType'];
		$pay_token = $arInput['payToken'];

		if (!in_array($pay_type, $this::PAYMENT_TYPE)) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			CModule::IncludeModule("sale");

			$Order = new CSaleOrder;

			$arOrder = $Order->GetByID($OrderNumber);

			if($pay_type == 'cash' and $arOrder['PAY_SYSTEM_ID'] == 3)
			{
				$arFields = array(
					"PAY_SYSTEM_ID" => '1'
				);
				if($arOrder['PS_STATUS_CODE'] != 'Hold' and $arOrder['PS_STATUS_CODE'] != 'Pay'){
					$Order->Update($arOrder["ID"], $arFields);
				}

				CModule::IncludeModule("iblock");

				define("OLD_ORDERS_EXPORT_DIR", $_SERVER["DOCUMENT_ROOT"] . "/in");
				CApi::import('COrdersExport');
				$ordersExport = new COrdersExport(OLD_ORDERS_EXPORT_DIR);

				$arTemp = $ordersExport->exportOrder($OrderNumber);

				$arResult['change_result'] = true;
				return $arResult;
			}
			//!тут будем менять платежную систему при необходимости

			//От тут делаем проверку на выгруженность заказа
			//Если он уже ушел в САП, то оплатить не даем

			//собираем пользовательские свойства заказа
			$db_props = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
					"ORDER_ID" => $arOrder["ID"],
					"CODE" => "export_for_sap"
				)
			);
			while ($arProps__ = $db_props->Fetch())
			{
				$db_props_ = CSaleOrderPropsValue::GetList(
					array("SORT" => "ASC"),
					array(
						"ORDER_ID" => $arOrder["ID"],
						"ORDER_PROPS_ID" => $arProps__['ID'],
					)
				);

				if ($arProps___ = $db_props_->Fetch())
				{
					$arOrder["PROPERTIES"][$arProps___['CODE']] = $arProps___;
				}
			}
			//!собираем пользовательские свойства заказа
			if($arOrder["PROPERTIES"]['export_for_sap']['VALUE'] != 'Y')
			{
				CModule::IncludeModule("webfly.sbrf");

				require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/webfly.sbrf/payment/sbrf.php");

				//для работы с модулем платежной системы необходимо получить все его параметры
				$dbPaySysAction = CSalePaySystemAction::GetList(
						array(),
						array(
								"PAY_SYSTEM_ID" => '3',
								"PERSON_TYPE_ID" => '1',
							),
						false,
						false,
						array("ACTION_FILE", "PARAMS", "ENCODING")
					);

				if ($arPaySysAction = $dbPaySysAction->Fetch())
				{
					if(!isset($GLOBALS["SALE_INPUT_PARAMS"]))
					$GLOBALS["SALE_INPUT_PARAMS"] = array();

					if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
					{
						$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);
					}
				}
				IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/webfly.sbrf/payment/result_rec.php');
				//!для работы с модулем платежной системы необходимо получить все его параметры

				$PayAction = new CSalePaySystemAction();

				// echo "<pre>";print_r($arOrder);echo "</pre>"."\r\n";

				$UserName = $PayAction->GetParamValue("USER_NAME");
				$Password = $PayAction->GetParamValue("PASSWORD");
				$TestMode = $PayAction->GetParamValue("TEST_MODE", "Y");
				$Stages = $PayAction->GetParamValue("STAGES", "Pay");
				// $FinalUrl = $PayAction->GetParamValue("FINAL_URL", "http://" . $_SERVER["HTTP_HOST"] . "/personal/order/payment/result.php");
				$FinalUrl = "https://" . $_SERVER["HTTP_HOST"] . "/pay_result/index.php?orderNumber=" . $OrderNumber;
				$Amount = ($arOrder['PRICE'] - $arOrder['SUM_PAID']) * 100;

				if ($TestMode == "Y")
				{
					$test_mode = true;
				}
				else
				{
					$test_mode = false;
				}
				if ($Stages == "Hold")
				{
					$two_stage = true;
				}
				else
				{
					$two_stage = false;
				}

				$sbrf = new SBRF($UserName, $Password, $two_stage, $test_mode);

				//TODO: два варианта развития событий:
				//либо вклиниться в текущий цикл, для возможности обхода возможных ошибок
				//либо полностью разделить код ifом
				/**
				 * Request register.do (one stage) or regiterPreAuth.do (two stages)
				 */
				for ($i = 0; $i <= 10; $i++)
				{
					$OrderNumberDesc = $OrderNumber . '_' . $i;
					// echo "<pre>";print_r(array($OrderNumberDesc, $Amount, $FinalUrl));echo "</pre>"."\r\n";
					switch ($pay_type) {
						case 'cashless':
							$response = $sbrf->register_order($OrderNumberDesc, $Amount, $FinalUrl);
							break;
						case 'applepay':
							// log_(array($OrderNumberDesc, $pay_token, 'applepay'));
							$response = $sbrf->payment($OrderNumberDesc, $pay_token, 'applepay');
							// log_($response);
							// log_('------------------------------------------------------------');
							break;
						case 'android':
							// log_(array($OrderNumberDesc, $pay_token, 'applepay'));
							$response = $sbrf->payment($OrderNumberDesc, $pay_token, 'android');
							// log_($response);
							// log_('------------------------------------------------------------');
							break;
						
						default:
							$this->addError('required_params_missed');
							break;
					}
					
					// echo "<pre>";print_r($response);echo "</pre>"."\r\n";
					if (($response['errorCode'] != 1 and $pay_type == 'cashless') or ($response['success'] == 1 and $pay_type == 'applepay') or ($response['success'] == 1 and $pay_type == 'android'))
						break;
				}

				/**
				 * Parse Answer
				 */
				switch ($pay_type) {
					case 'cashless':
						if ($response['errorCode'] != 0)
						{
							$error = GetMessage("WF.SBRF_ERROR") . self::toWIN($response['errorCode']) . ': ' . self::toWIN($response['errorMessage']);
							$arResult['error'] = $error;
						}
						else if ($response['errorCode'] == 0)
						{
							if($arOrder["PS_STATUS_CODE"] != "Hold")
							{
								$arFieldsBlock = array(
								  "PS_STATUS_DESCRIPTION" => "OrderNumber:" . $OrderNumberDesc,
								  "PAY_VOUCHER_NUM" => $response['orderId'],
								  "PAY_VOUCHER_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
								);

								$Order->Update($OrderNumber, $arFieldsBlock);

								$_SESSION['WF_SBRF_ORDER_NUMBER'] = $OrderNumber;

								$arResult['formUrl'] = $response['formUrl'];
							}
						}
						else
						{
							$error = GetMessage("WF.SBRF_ERROR_UNKNOWN");
							$arResult['error'] = $error;
						}
						break;
					case 'applepay':
					case 'android':
						if (
							(isset($response['data']) && is_array($response['data'])) &&
							(isset($response['orderStatus']) && is_array($response['orderStatus'])) &&
							(($response['orderStatus']['orderStatus'] == 1) || ($response['orderStatus']['orderStatus'] == 2))
							)
						{
							$title = GetMessage("WF.SBRF_PAY_TITLE");
							// Save order info
							if (($response['orderStatus']['orderStatus'] == 1))//hold
							{
								$arFieldsBlock = array(
								  "PS_SUM" => $response['orderStatus']["amount"] / 100,
								  "PS_CURRENCY" => $sbrf->getCurrenciesISO($response['orderStatus']["currency"]),
								  "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
								  "PAYED" => "N",
								  "PS_STATUS" => "N",
								  "PS_STATUS_CODE" => "Hold",
								  "PS_STATUS_DESCRIPTION" => GetMessage("WF.SBRF_PS_CURSTAT") . GetMessage("WF.SBRF_PS_STATUS_DESC_HOLD") . "; " . GetMessage("WF.SBRF_PS_CARDNUMBER") . $response['orderStatus']["cardAuthInfo"]["pan"] . "; " . GetMessage("WF.SBRF_PS_CARDHOLDER") . $response['orderStatus']['cardAuthInfo']["cardholderName"] . "; OrderNumber:" . $response['orderStatus']['orderNumber'],
								  "PS_STATUS_MESSAGE" => $response['orderStatus']["paymentAmountInfo"]["paymentState"],
								  "PAY_VOUCHER_NUM" => $response['data']['orderId'], //дописываем айдишник транзакции к заказу, чтоб потом передать в сап
								  "PAY_VOUCHER_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
								);
								$Order->Update($OrderNumber, $arFieldsBlock);
							}
							if (($response['orderStatus']['orderStatus'] == 2))//success
							{
								$arFieldsSuccess = array(
								  "PS_SUM" => $response['orderStatus']["amount"] / 100,
								  "PS_CURRENCY" => $sbrf->getCurrenciesISO($response['orderStatus']["currency"]),
								  "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
								  "PAYED" => "Y",
								  // "STATUS_ID" => "P",
								  "PS_STATUS" => "Y",
								  "PS_STATUS_CODE" => "Pay",
								  "PS_STATUS_DESCRIPTION" => GetMessage("WF.SBRF_PS_CURSTAT") . GetMessage("WF.SBRF_PS_STATUS_DESC_PAY") . "; " . GetMessage("WF.SBRF_PS_CARDNUMBER") . $response['orderStatus']["cardAuthInfo"]["pan"] . "; " . GetMessage("WF.SBRF_PS_CARDHOLDER") . $response['orderStatus']['cardAuthInfo']["cardholderName"] . "; OrderNumber:" . $response['orderStatus']['orderNumber'],
								  "PS_STATUS_MESSAGE" => self::toWIN($response['orderStatus']["paymentAmountInfo"]["paymentState"]),
								  "PAY_VOUCHER_NUM" => $response['data']['orderId'], //дописываем айдишник транзакции к заказу, чтоб потом передать в сап
								  "PAY_VOUCHER_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
								);
								$Order->PayOrder($OrderNumber, "Y", true, true);
								$Order->Update($OrderNumber, $arFieldsSuccess);
								// $message = GetMessage("WF.SBRF_PAY_SUCCESS_TEXT", array("#ORDER_ID#" => $arOrder["ID"]));
							}
						}
						else
						{
							$arOrderFields = array(
								"PAYED" => "N",
								"PS_STATUS" => "N",
								"PS_STATUS_MESSAGE" => "[" . self::toWIN($response['error']["code"]) . "] " . self::toWIN($response['error']["message"])
							);
							$Order->Update($OrderNumber, $arOrderFields);
						}

						$arResult['formUrl'] = "https://" . $_SERVER["HTTP_HOST"] . "/pay_result/index.php?orderNumber=" . $OrderNumber . "&orderId=" . $response['data']['orderId'];
						break;
					
					default:
						$this->addError('required_params_missed');
						break;
				}
			}
			else
			{
				$this->addError('order_already_exported');
			}
			//!От тут делаем проверку на выгруженность заказа
			//!Если он уже ушел в САП, то оплатить не даем

		}

		return $arResult;
	}
}
