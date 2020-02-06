<?
$MESS["RESULT_ORDER_STATUS"] = "Result order status";
$MESS["CHECK_HTTPS"] = "Check PHP, cURL, TLS";
//$MESS['CHECK_HTTPS_SUCCESS'] = 'TLS works';
//$MESS['CHECK_HTTPS_FAIL'] = "TLS doesn't work";
$MESS['CURRENCY_CHOISE'] = "Currency choise";
$MESS['CC_HEAD_CURRENCY'] = "Currency";
$MESS['CC_HEAD_CODE'] = "Code";
$MESS['CC_HEAD_ISO'] = "ISO";
$MESS['BANK_ISSUED_CHECK'] = "The check is issued by the bank";
$MESS['BANK_ISSUED_CHECK_DESCRIPTION'] = "If the value is 'Y', it will generate and send a check to the client. The option is paid, please contact the bank's after-sales service for connection. <br> When using, it is necessary to set up VAT for goods sold В»";
$MESS['TAX_SYSTEM'] = "Tax system";
$MESS['TAX_SYSTEM_GENERAL'] = "General";
$MESS['TAX_SYSTEM_SIMPLIFIED_INCOME'] = "Simplified income";
$MESS['TAX_SYSTEM_SIMPLIFIED_REVENUE_MINUS_CONSUMPTION'] = "Simplified revenue minus consumption";
$MESS['TAX_SYSTEM_SINGLE_TAX_ON_IMPUTED_INCOME'] = "A single tax on imputed income";
$MESS['TAX_SYSTEM_UNIFIED_AGRICULTURAL_TAX'] = "Unified agricultural tax";
$MESS['TAX_SYSTEM_PATENT_SYSTEM_OF_TAXATION'] = "Patent system of taxation";

$MESS['ADVANCED_OPTIONS_TITLE'] = "Advanced options";
$MESS['RETURN_PAGE_LABEL'] = "Return page";
$MESS['RETURN_PAGE_DESCRIPTION'] = "Change only if you need to implement your own payment processing logic. Without parameters. <br> By default - <b> /sale/payment/result.php </ b>";

$MESS['GATE_SEND_COMMENT_LABEL'] = "Data in the commentary";
$MESS['GATE_SEND_COMMENT_NAME_FIO'] = "Name";
$MESS['GATE_SEND_COMMENT_NAME_COMMENT'] = "Comment";
$MESS['GATE_SEND_COMMENT_DESCRIPTION'] = "Fields that will be transferred to the bank's private office. <br> By default, <b> Comment </ b> is always passed to the order";
$MESS['GATE_TRY_LABEL'] = "Number of attempts";
$MESS['GATE_TRY_DESCRIPTION'] = "The number of attempts to register an order at the gateway. After the customer of the store gets on the page of the successful registration of the order. The order is registered on the payment gateway, if the user updates the page, a new attempt will be registered. Default: 30 attempts.";
?>