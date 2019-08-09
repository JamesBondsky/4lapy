<?php

/**
 * @copyright Copyright (c) NotAgency
 */


namespace FourPaws\MobileApiBundle\Services\Api;

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php';

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Iblock\ElementTable;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\PaymentService as AppPaymentService;
use FourPaws\PersonalBundle\Repository\OrderRepository;

class PaymentService
{
    use LazyLoggerAwareTrait;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var AppPaymentService
     */
    private $appPaymentService;


    public function __construct(
        OrderRepository $orderRepository,
        AppPaymentService $appPaymentService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->appPaymentService = $appPaymentService;
    }

    /**
     * @param int $orderNumber
     * @param string $payType
     * @param string $payToken
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Exception
     */
    public function getPaymentUrl(int $orderNumber, string $payType, string $payToken = ''): string
    {
        /**
         * @var $order Order
         */
        $order = $this->orderRepository->findBy([
            'filter' => [
                'ACCOUNT_NUMBER' => $orderNumber
            ]
        ])->current();
        if (!$order) {
            throw new NotFoundException("Заказ с номером $orderNumber не найден");
        }

        $bitrixOrder = $order->getBitrixOrder();
        $amount = $order->getBitrixOrder()->getPrice() * 100;

        if (!$bitrixOrder->getPaymentCollection()->count()) {
            throw new \Exception("У заказа $orderNumber не указана платежная система");
        }

        if (!$this->appPaymentService->isOnlinePayment($bitrixOrder)) {
            throw new \Exception("У заказа $orderNumber не выбран способ оплаты - онлайн");
        }

        $url = '';

        switch ($payType) {
            case 'cash':
            case 'cashless':
                $url = $this->appPaymentService->registerOrder($bitrixOrder, $amount, true);
                break;
            case 'applepay':
                $url = $this->appPaymentService->processApplePay($bitrixOrder, $payToken);
                /*if ($response['error']) {
                    throw new \Exception($response['error']['message'], $response['error']['code']);
                }*/
                break;
            case 'android':
                $response = $this->appPaymentService->processGooglePay($bitrixOrder, $payToken, $amount);
                if ($response['error']) {
                    throw new \Exception($response['error']['message'], $response['error']['code']);
                }
                break;
            default:
                throw new \Exception("Unsupported pay type " . $payType);
                break;
        }

        try {
            //TODO вынести общий с \FourPaws\SaleBundle\Service\PaymentService::processOnlinePayment код
            // Костыль, дублирующий код с сайта для добавления магнитика в МП.
            // Причем магнит в данном случае добавляется при переходе пользователя к оплате (согласовано), но лучше было бы найти,
            // как сайт узнает об оплате в МП, и перенести добавление магнита туда
            $deliveryService = Application::getInstance()->getContainer()->get(DeliveryService::class);

            /** получаем код доставки */
            $deliveryId = $bitrixOrder->getField('DELIVERY_ID');
            $deliveryCode = $deliveryService->getDeliveryCodeById($deliveryId);
            if ($deliveryService->isDobrolapDeliveryCode($deliveryCode)) {
                $magnetID = ElementTable::getList([
                    'select' => ['ID', 'XML_ID'],
                    'filter' => ['XML_ID' => BasketService::GIFT_DOBROLAP_XML_ID, 'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)],
                    'limit'  => 1,
                ])->fetch()['ID'];

                if ($magnetID && $userId = $bitrixOrder->getUserId()) {
                    /** @var BasketService $basketService */
                    $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
                    $basketItem = $basketService->addOfferToBasket(
                        (int)$magnetID,
                        1,
                        [],
                        true,
                        $basketService->getBasket()
                    );
                    /** если магнит успешно добавлен в корзину */
                    if ($basketItem->getId()) {
                        $userDB = new \CUser;
                        $fields = [
                            'UF_GIFT_DOBROLAP' => false
                        ];
                        $userDB->Update($userId, $fields);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->critical(__METHOD__ . '. Не удалось добавить магнитик в корзину пользователя ' . $userId . ' по заказу ' . $orderNumber);
        }

        return $url;
    }
}
