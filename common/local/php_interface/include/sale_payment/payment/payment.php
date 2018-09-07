<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;
use FourPaws\App\Application as PawsApplication;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\PaymentService;

IncludeModuleLangFile(__FILE__);

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_class.php';

/**
 * Подключение файла настроек
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php';


/** @noinspection PhpDeprecationInspection */
$paySystemAction = new CSalePaySystemAction();

$test_mode = $paySystemAction->GetParamValue('TEST_MODE') === 'Y';
$two_stage = $paySystemAction->GetParamValue('TWO_STAGE') === 'Y';
$logging = $paySystemAction->GetParamValue('LOGGING') === 'Y';
$password = $paySystemAction->GetParamValue('PASSWORD');
$user_name = $paySystemAction->GetParamValue('USER_NAME');

/** @noinspection PhpMethodParametersCountMismatchInspection */
$rbs = new RBS(\compact('test_mode', 'two_stage', 'logging', 'user_name', 'password'));

/** @noinspection PhpUnhandledExceptionInspection */
$app = Application::getInstance();
$request = $app->getContext()->getRequest();

$entityId = $paySystemAction->GetParamValue('ORDER_PAYMENT_ID');
$amount = $paySystemAction->GetParamValue('AMOUNT') * 100;

[$orderId, $paymentId] = Manager::getIdsByPayment($entityId);

$order = Order::load($orderId);

if (is_float($amount)) {// Если сумма с плавающей точкой
    $amount = ceil($amount); // Производим округление в большую сторону
}

$returnUrl = '/sale/payment/result.php?ORDER_ID=' . $order->getId();
$returnUrl .= '&HASH=' . $order->getHash();

$fiscalization = COption::GetOptionString('sberbank.ecom', 'FISCALIZATION', serialize([]));
/** @noinspection UnserializeExploitsInspection */
$fiscalization = unserialize($fiscalization, []);

/* Фискализация */
$fiscal = [];
if ($fiscalization['ENABLE'] === 'Y') {
    /**
     * @var PaymentService $paymentService
     * @global $USER
     */
    $paymentService = PawsApplication::getInstance()->getContainer()->get(PaymentService::class);
    $fiscal = $paymentService->getFiscalization($order, (int)$fiscalization['TAX_SYSTEM']);
    $amount = $paymentService->getFiscalTotal($fiscal);
    $fiscal = $paymentService->fiscalToArray($fiscal)['fiscal'];
}
/* END Фискализация */
$response = $rbs->register_order(
    $order->getField('ACCOUNT_NUMBER'),
    $amount,
    (string)new FullHrefDecorator($returnUrl),
    $order->getCurrency(),
    $order->getField('USER_DESCRIPTION'),
    $fiscal
);

switch ((int)$response['errorCode']) {
    case 0:
        $formUrl = $response['formUrl'];
        break;
    case 1:
        try {
            $orderInfo = $paymentService->getSberbankOrderStatusByOrderNumber($order->getField('ACCOUNT_NUMBER'));
            $formUrl = $paymentService = $paymentService->getSberbankPaymentUrl($orderInfo);
            break;
        } catch (\FourPaws\SaleBundle\Exception\SberbankOrderNotFoundException $e) {
            // обработка ниже
        }
        // no break
    default:
        throw new PaymentException(
            $response['errorMessage'] ?? GetMessage('RBS_PAYMENT_PAY_ERROR'),
            $response['errorCode']
        );
}

echo '<script>window.location="' . $formUrl . '"</script>';
