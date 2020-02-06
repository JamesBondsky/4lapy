<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule('sale')) return;

require_once(realpath(dirname(dirname(__FILE__))) ."/config.php");

$isOrderConverted = \Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'N');
$errorMessage = '';

$arUser = CUser::GetByID($USER->GetID())->Fetch();
$arOrder = CSaleOrder::GetByID($_REQUEST["ID"]);

$checkParams = true;
if(empty($_REQUEST["ID"]) || empty($_REQUEST["orderId"]) || empty($arOrder) ) {
    $checkParams = false;   
}

if ($checkParams) {

    $order_id = $_GET["orderId"];
    $order_number = $_REQUEST["ID"];

    if ($arOrder['PAYED'] == "N") {

        $paySystem = new CSalePaySystemAction();
        $paySystem->InitParamArrays($arOrder, $arOrder["ID"]);
        $order_number = $arOrder["ID"];

        require_once("rbs.php");

        if ($paySystem->GetParamValue("TEST_MODE") == 'Y') {
            $test_mode = true;
        } else {
            $test_mode = false;
        }

        if ($paySystem->GetParamValue("LOGGING") == 'Y') {
            $logging = true;
        } else {
            $logging = false;
        }

        $params['user_name'] = $paySystem->GetParamValue("USER_NAME");
        $params['password'] = $paySystem->GetParamValue("PASSWORD");
        $params['test_mode'] = $test_mode;
        $params['logging'] = $logging;

        $rbs = new RBS($params);

        $response = $rbs->get_order_status_by_orderId($order_id);

        if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {

            $arOrderFields = array(
                "PS_SUM" => $response["amount"] / 100,
                "PS_CURRENCY" => $response["currency"],
                "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
                "PS_STATUS" => "Y",
                "PS_STATUS_DESCRIPTION" => $response["cardAuthInfo"]["pan"] . ";" . $response['cardAuthInfo']["cardholderName"],
                "PS_STATUS_MESSAGE" => $response["paymentAmountInfo"]["paymentState"],
                "PS_STATUS_CODE" => "Y",
            );

            CSaleOrder::StatusOrder($order_number, RESULT_ORDER_STATUS);
            CSaleOrder::PayOrder($order_number, "Y", true, true);

            if ($paySystem->GetParamValue("SHIPMENT_ENABLE") == 'Y') {
                if ($isOrderConverted != "Y") {
                    CSaleOrder::DeliverOrder($order_number, "Y");
                } else {
                    $r = \Bitrix\Sale\Compatible\OrderCompatibility::allowDelivery($order_number, true);
                    if (!$r->isSuccess(true)) {
                        foreach ($r->getErrorMessages() as $error) {
                            $errorMessage .= " " . $error;
                        }
                    }
                }
            }

            $orderNumberPrint = $paySystem->GetParamValue('ORDER_NUMBER');

            $title = GetMessage('RBS_PAYMENT_ORDER_THANK');
            if ($response['orderStatus'] == 1) {
                $message = GetMessage('RBS_PAYMENT_ORDER_AUTH', array('#ORDER_ID#' => $orderNumberPrint));
            } else {
                $message = GetMessage('RBS_PAYMENT_ORDER_FULL_AUTH', array('#ORDER_ID#' => $orderNumberPrint));
            }

            $title = GetMessage('RBS_PAYMENT_ORDER_THANK');
            $message = GetMessage('RBS_PAYMENT_ORDER_PAY1', array('#ORDER_ID#' => $order_number));

        } else if ($response['errorCode'] == 0) {
            $arOrderFields["PS_STATUS_MESSAGE"] = "[" . $response["orderStatus"] . "] " . $response["actionCodeDescription"];
            $title = GetMessage('RBS_PAYMENT_ORDER_PAY', array('#ORDER_ID#' => $orderNumberPrint));
            $message = GetMessage('RBS_PAYMENT_ORDER_STATUS', array('#ORDER_ID#' => $response["orderStatus"], '#DESCRIPTION#' => $response["actionCodeDescription"]));

        } else {
            $arOrderFields["PS_STATUS_MESSAGE"] = GetMessage('RBS_PAYMENT_ORDER_ERROR', array('#ERROR_CODE#' => $response["errorCode"], '#ERROR_MESSAGE#' => $response["errorMessage"]));
            $title = GetMessage('RBS_PAYMENT_ORDER_PAY', array('#ORDER_ID#' => $orderNumberPrint));
            $message = GetMessage('RBS_PAYMENT_ORDER_ERROR2', array('#ERROR_CODE#' => $response["errorCode"], '#ERROR_MESSAGE#' => $response["errorMessage"]));
        }

        CSaleOrder::Update($order_number, $arOrderFields);

    } else {

        $title = GetMessage('RBS_PAYMENT_ORDER_THANK');
        $message = GetMessage('RBS_PAYMENT_ORDER_PAY1', array('#ORDER_ID#' => $order_number));

    }


} else {
    $title = GetMessage('RBS_PAYMENT_ORDER_ERROR3');
    $message = GetMessage('RBS_PAYMENT_ORDER_NOT_FOUND', array('#ORDER_ID#' => htmlspecialchars(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('ORDER_ID'), ENT_QUOTES)));
}

$APPLICATION->SetTitle($title);
echo $message;
