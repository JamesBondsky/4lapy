<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Sale\Order;
use Bitrix\Main\Loader;
use FourPaws\App\Application;
use Bitrix\Sale\PropertyValue;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use FourPaws\Helpers\BxCollection;
use Bitrix\Main\ArgumentNullException;
use FourPaws\StoreBundle\Entity\Store;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use GuzzleHttp\Exception\GuzzleException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreBundleNotFoundException;

global $USER;

if (!$USER->IsAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Скрипт доступен только администраторам!'
    ]);
    return;
}

if (!check_bitrix_sessid()) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка проверки сессии!'
    ]);
    return;
}

if (!isset($_POST['order_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Номер заказа не передан!'
    ]);
    return;
}

$orderCode = $_POST['order_code'];

try {
    Loader::includeModule('sale');
} catch (LoaderException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

/**
 * @var Order $order
 */
try {
    $order = Order::loadByAccountNumber($orderCode);
} catch (ArgumentNullException|ArgumentException|NotImplementedException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

if (!$order instanceof Order) {
    echo json_encode([
        'success' => false,
        'message' => 'Заказ не найден!'
    ]);
    return;
}

/**
 * @var StoreService $storeService
 */
$storeService = Application::getInstance()->getContainer()->get(StoreService::class);
/**
 * @var OrderService $orderService
 */
$orderService = Application::getInstance()->getContainer()->get(OrderService::class);
/**
 * @var DeliveryService $deliveryService
 */
$deliveryService = Application::getInstance()->getContainer()->get(DeliveryService::class);

$deliveryId = $order->getField('DELIVERY_ID');

try {
    $deliveryCode = $deliveryService->getDeliveryCodeById($deliveryId);
} catch (ObjectPropertyException|NotFoundException|SystemException|ArgumentException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

if (!$deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип доставки в заказе!'
    ]);
    return;
}

$isPaid = $order->isPaid();

try {
    $orderPropertyCollection = $order->getPropertyCollection();
} catch (ArgumentException|NotImplementedException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

$comments = $order->getField('USER_DESCRIPTION');
if (is_null($comments)) {
    $comments = '';
}

/** @var PropertyValue $item */
foreach ($orderPropertyCollection as $item) {
    switch ($item->getProperty()['CODE']) {
        case 'STORE_FOR_DOSTAVISTA':
            $storeXmlId = $item->getValue();
            break;
        case 'NAME':
            $name = $item->getValue();
            break;
        case 'PHONE':
            $phone = $item->getValue();
            break;
    }
}

/** @var Store $selectedStore */
try {
    $nearShop = $storeService->getStoreByXmlId($storeXmlId);
} catch (StoreBundleNotFoundException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

try {
    $deliveryData = ServicesTable::getById($deliveryId)->fetch();
} catch (ObjectPropertyException|ArgumentException|SystemException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

$periodTo = $deliveryData['CONFIG']['MAIN']['PERIOD']['TO'];
$address = $orderService->compileOrderAddress($order)->setValid(true);


if (!$name) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип доставки в заказе!'
    ]);
    return;
}
if (!$phone) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип доставки в заказе!'
    ]);
    return;
}
if (!$periodTo) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип доставки в заказе!'
    ]);
    return;
}
if (!$nearShop) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип доставки в заказе!'
    ]);
    return;
}

try {
    $isExportedToQueue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'IS_EXPORTED_TO_DOSTAVISTA_QUEUE')->getValue();
    $orderService->sendToDostavistaQueue($order, $name, $phone, $comments, $periodTo, $nearShop, $isPaid);
} catch (ArgumentException|NotImplementedException|SystemException|AddressSplitException|GuzzleException $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
    return;
}

echo json_encode([
    'success' => true,
    'message' => 'Заказ добавлен в очередь!'
]);