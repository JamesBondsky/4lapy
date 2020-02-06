<?
$MESS["RESULT_ORDER_STATUS"] = "Статус заказа при успешной оплате";
$MESS["CHECK_HTTPS"] = "Показать версии PHP; cURL; TLS";
$MESS['CURRENCY_CHOISE'] = "Выбор валют";
$MESS['CC_HEAD_CURRENCY'] = "Валюта";
$MESS['CC_HEAD_CODE'] = "Код";
$MESS['CC_HEAD_ISO'] = "ISO";
$MESS['BANK_ISSUED_CHECK'] = "Чек выпускает банк";
$MESS['BANK_ISSUED_CHECK_DESCRIPTION'] = "«Если значение 'Y', то сформирует и отправит клиенту чек. Опция платная, <br>за подключением обратитесь в сервисную службу банка. <br>При использовании необходимо настроить НДС продаваемых товаров»";
$MESS['TAX_SYSTEM'] = "Система налогообложения";
$MESS['TAX_SYSTEM_GENERAL'] = "Общая";
$MESS['TAX_SYSTEM_SIMPLIFIED_INCOME'] = "Упрощённая, доход";
$MESS['TAX_SYSTEM_SIMPLIFIED_REVENUE_MINUS_CONSUMPTION'] = "Упрощённая, доход минус расход";
$MESS['TAX_SYSTEM_SINGLE_TAX_ON_IMPUTED_INCOME'] = "Единый налог на вменённый доход";
$MESS['TAX_SYSTEM_UNIFIED_AGRICULTURAL_TAX'] = "Единый сельскохозяйственный налог";
$MESS['TAX_SYSTEM_PATENT_SYSTEM_OF_TAXATION'] = "Патентная система налогообложения";
$MESS['TAB1_CURRENCY_TITLE'] = "Валюты";
$MESS['TAB1_FISCALIZATION_TITLE'] = "Чек";
$MESS['TAB1_VAT_TITLE'] = "Ставки НДС, для товаров";
$MESS['TAB1_VAT_NOT_SET'] = "-не выбрано-";
$MESS['TAB1_VAT_LIST_VALUE_0'] = "Без НДС";
$MESS['TAB1_VAT_LIST_VALUE_1'] = "НДС 0%";
$MESS['TAB1_VAT_LIST_VALUE_2'] = "НДС 10%";
$MESS['TAB1_VAT_LIST_VALUE_3'] = "НДС 18%";
$MESS['TAB1_VAT_DELIVERY_TITLE'] = "Ставки НДС, для доставки";
$MESS['ADVANCED_OPTIONS_TITLE'] = "Продвинутые опции";
$MESS['RETURN_PAGE_LABEL'] = "Страница возврата";
$MESS['RETURN_PAGE_DESCRIPTION'] = "Изменять только в том случае, если вам необходимо <br>реализовать собственную логику обработки платежа. Без параметров. <br> По умолчанию - <b>/sale/payment/result.php</b>";

$MESS['GATE_SEND_COMMENT_LABEL'] = "Комментарии";
$MESS['GATE_SEND_COMMENT_NAME_FIO'] = "ФИО покупателя";
$MESS['GATE_SEND_COMMENT_NAME_COMMENT'] = "Комментарий к заказу";
$MESS['GATE_SEND_COMMENT_DESCRIPTION'] = "Поля которые будут переданы в личный кабинет банка в поле description. <br>По умолчанию, всегда передается <b>комментарий</b> к заказу";

$MESS['GATE_TRY_LABEL'] = "Количество попыток";
$MESS['GATE_TRY_DESCRIPTION'] = "Количество попыток регистрации заказа на шлюзе. После того как клиент магазина попадает на страницу успешного оформления заказа. Заказ регистрируется на платежноном шлюзе, если пользователь будет обновлять страницу, то будет регистрироваться новая попытка. По умолчанию: 30 попыток.";
?>