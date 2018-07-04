<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use CUser;
use FourPaws\DeliveryBundle\Entity\Terminal;
use FourPaws\Helpers\BusinessValueHelper;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Exception\PaymentReverseException;
use FourPaws\SaleBundle\Payment\Sberbank;
use FourPaws\StoreBundle\Entity\Store;

/**
 * Class PaymentService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PaymentService
{
    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var Sberbank
     */
    private $sberbankProcessing;

    /**
     * PaymentService constructor.
     * @param BasketService $basketService
     */
    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @param Store $store
     * @return array
     */
    public function getAvailablePaymentsForStore(Store $store): array
    {
        $result = [OrderService::PAYMENT_ONLINE];
        if ($store instanceof Terminal) {
            if ($store->isNppAvailable()) {
                if ($store->hasCardPayment()) {
                    $result[] = OrderService::PAYMENT_CASH_OR_CARD;
                } elseif ($store->hasCashPayment()) {
                    $result[] = OrderService::PAYMENT_CASH;
                }
            }
        } else {
            $result[] = OrderService::PAYMENT_CASH_OR_CARD;
        }

        return $result;
    }

    /**
     * @todo переделать на DTO
     * @todo переделать на сериализацию
     *
     * @param Order       $order
     * @param CUser|array $user
     * @param int         $taxSystem
     * @param bool        $skipGifts
     *
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getFiscalization(Order $order, $user, int $taxSystem, $skipGifts = true): array
    {
        $amount = 0; //Для фискализации общая сумма берется путем суммирования округленных позиций.
        if ($user instanceof \CUser) {
            $userEmail = $user->GetEmail();
            $userName = $user->GetFullName();
        } else {
            $userEmail = ['email'];
            $userName = ['name'];
        }

        $fiscal = [
            'orderBundle' => [
                'orderCreationDate' => \strtotime($order->getField('DATE_INSERT')),
                'customerDetails'   => [
                    'email'   => false,
                    'contact' => false,
                ],
                'cartItems'         => [
                    'items' => [],
                ],
            ],
            'taxSystem'   => $taxSystem,
        ];

        /** @var \Bitrix\Sale\PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getProperty()['IS_PAYER'] === 'Y') {
                $fiscal['orderBundle']['customerDetails']['contact'] = $propertyValue->getValue();
            } elseif ($propertyValue->getProperty()['IS_EMAIL'] === 'Y') {
                $fiscal['orderBundle']['customerDetails']['email'] = $propertyValue->getValue();
            }
        }

        if (!$fiscal['orderBundle']['customerDetails']['email'] || !$fiscal['orderBundle']['customerDetails']['contact']) {
            if (!$fiscal['orderBundle']['customerDetails']['email']) {
                $fiscal['orderBundle']['customerDetails']['email'] = $userEmail;
            }
            if (!$fiscal['orderBundle']['customerDetails']['contact']) {
                $fiscal['orderBundle']['customerDetails']['contact'] = $userName;
            }
        }

        $measureList = [];
        $dbMeasure = \CCatalogMeasure::getList();
        while ($arMeasure = $dbMeasure->GetNext()) {
            $measureList[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
        }

        $vatList = [];
        $dbRes = \CCatalogVat::GetListEx();
        while ($arRes = $dbRes->Fetch()) {
            $vatList[$arRes['ID']] = (int)$arRes['RATE'];
        }

        $vatGateway = [
            -1 => 0,
            0  => 1,
            10 => 2,
            18 => 3,
        ];

        $itemsCnt = 0;
        $arCheck = null;
        $itemMap = [];

        $cartItems = [];
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($order->getBasket() as $basketItem) {
            // пропускаем подарки
            $xmlId = $this->basketService->getBasketItemXmlId($basketItem);
            if ($skipGifts && $xmlId[0] === '3') {
                continue;
            }

            $arProduct = \CCatalogProduct::GetByID($basketItem->getProductId());
            $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

            $itemAmount = $basketItem->getPrice() * 100;
            if (!($itemAmount % 1)) {
                $itemAmount = \round($itemAmount);
            }

            $amount += $itemAmount * $basketItem->getQuantity(); //Для фискализации общая сумма берется путем суммирования округленных позиций.

            $cartItems[] = [
                'positionId' => ++$itemsCnt,
                'name'       => $basketItem->getField('NAME'),
                'quantity'   => [
                    'value'   => (int)$basketItem->getQuantity(),
                    'measure' => $measureList[$arProduct['MEASURE']],
                ],
                'itemAmount' => (int)($itemAmount * $basketItem->getQuantity()),
                'itemCode'   => (int)$basketItem->getProductId(),
                'itemPrice'  => (int)$itemAmount,
                'tax'        => [
                    'taxType' => $vatGateway[$taxType],
                ],
            ];

            if (!isset($itemMap[$xmlId])) {
                $itemMap[$xmlId] = [
                    'id' => (int)$basketItem->getProductId(),
                    'count' => 0
                ];
            }
            $itemMap[$xmlId]['count']++;
        }

        $delivery = null;
        if ($order->getDeliveryPrice() > 0) {
            $delivery = [
                'positionId' => $itemsCnt + 1,
                'name'       => Loc::getMessage('RBS_PAYMENT_DELIVERY_TITLE'),
                'quantity'   => [
                    'value'   => 1,
                    'measure' => Loc::getMessage('RBS_PAYMENT_MEASURE_DEFAULT'),
                ],
                'itemAmount' => $order->getDeliveryPrice() * 100,
                'itemCode'   => $order->getId() . '_DELIVERY',
                'itemPrice'  => $order->getDeliveryPrice() * 100,
                'tax'        => [
                    'taxType' => 0,
                ],
            ];
        }

        $innerPayment = $order->getPaymentCollection()->getInnerPayment();
        if ($innerPayment && $innerPayment->isPaid()) {
            $bonusSum = $innerPayment->getSum() * 100;
            $diff = $amount - $bonusSum;

            $correction = 0;
            foreach ($cartItems as $i => $item) {
                $cartItems[$i]['itemPrice'] = floor($item['itemPrice'] * ($diff / $amount));
                $oldAmount = $cartItems[$i]['itemAmount'];
                $cartItems[$i]['itemAmount'] = $cartItems[$i]['itemPrice'] * $cartItems[$i]['quantity']['value'];
                $correction += $oldAmount - $cartItems[$i]['itemAmount'];
            }

            /**
             * распределяем погрешность по товарам
             */
            $correction = $bonusSum - $correction;
            foreach ($cartItems as $i => $item) {
                if ((int)$correction === 0) {
                    break;
                }
                $quantity = $cartItems[$i]['quantity']['value'];

                $oldAmount = $cartItems[$i]['itemAmount'];
                $cartItems[$i]['itemPrice'] = floor(
                    $item['itemAmount'] * ($item['itemAmount'] - $correction) / $item['itemAmount'] / $quantity
                );
                $cartItems[$i]['itemAmount'] = $cartItems[$i]['itemPrice'] * $cartItems[$i]['quantity']['value'];
                $correction -= $oldAmount - $cartItems[$i]['itemAmount'];
            }

            /** погрешность все равно может не стать равной 0  */
            $amount += $correction;

            $amount -= $bonusSum;
        }

        if ($delivery) {
            $cartItems[] = $delivery;
            $amount += $order->getDeliveryPrice() * 100; //Для фискализации общая сумма берется путем суммирования округленных позиций.
        }

        $fiscal['orderBundle']['cartItems']['items'] = $cartItems;

        return \compact('amount', 'fiscal', 'itemMap');
    }

    /**
     * @param Order $order
     *
     * @return string
     *
     * @throws ObjectNotFoundException
     */
    public function getOrderInvoiceId(Order $order): string
    {
        $result = null;
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $result = $payment->getField('PS_INVOICE_ID');
            break;
        }

        return $result ?: '';
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws ObjectNotFoundException
     */
    public function isOnlinePayment(Order $order): bool
    {
        $result = false;
        try {
            $result = $this->getOrderPaymentType($order) === OrderService::PAYMENT_ONLINE;
        } catch (NotFoundException $e) {
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws NotFoundException
     * @return Payment
     */
    public function getOrderPayment(Order $order): Payment
    {
        $payment = null;
        /** @var Payment $orderPayment */
        foreach ($order->getPaymentCollection() as $orderPayment) {
            if ($orderPayment->isInner()) {
                continue;
            }

            $payment = $orderPayment;
        }

        if (null === $payment) {
            throw new NotFoundException('payment system is not defined');
        }

        return $payment;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @return string
     */
    public function getOrderPaymentType(Order $order): string
    {
        return $this->getOrderPayment($order)->getPaySystem()->getField('CODE');
    }

    /**
     * @param Order $order
     * @param float $amount
     * @param array $fiscalization
     *
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
     */
    public function depositPayment(Order $order, float $amount, array $fiscalization = []): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        if (empty($fiscalization)) {
            $fiscalization = $this->getFiscalization($order, null, 0);
        }
        return $this->response(function () use ($orderInvoiceId, $amount, $fiscalization) {
            return $this->getSberbankProcessing()->depositPayment($orderInvoiceId, $amount, $fiscalization);
        });
    }

    /**
     * @param Order $order
     * @param float $amount
     * @param bool  $save
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @throws SystemException
     * @throws \Exception
     */
    public function cancelPayment(Order $order, float $amount = 0, $save = true): void
    {
        if ($this->isOnlinePayment($order)) {
            $this->reverseOnlinePayment($order, $amount);
        }

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            $payment->setPaid(BitrixUtils::BX_BOOL_FALSE);
            if ($save) {
                $payment->save();
            }
        }

        if ($save) {
            $order->save();
        }
    }/** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Order $order
     * @param float $amount
     *
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws PaymentException
     */
    protected function reverseOnlinePayment(Order $order, float $amount = 0): void
    {
        if (!$this->getOrderPayment($order)->isPaid()) {
            return;
        }

        $orderInfo = $this->getSberbankProcessing()->getOrderStatusByOrderId($this->getOrderInvoiceId($order));
        $orderStatus = $orderInfo['orderStatus'];
        if ($orderStatus === Sberbank::ORDER_STATUS_HOLD) {
            $this->tryPaymentReverse($order);
        } elseif ($orderStatus === Sberbank::ORDER_STATUS_PAID) {
            $this->tryPaymentRefund($order, $orderInfo['amount']);
        } else {
            throw new PaymentReverseException(sprintf('Invalid order status: %s', $orderStatus));
        }
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
     */
    protected function tryPaymentReverse(Order $order): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        return $this->response(function () use ($orderInvoiceId) {
            return $this->getSberbankProcessing()->reversePayment($orderInvoiceId);
        });
    }

    /**
     * @param Order $order
     * @param int   $amount
     *
     * @throws ObjectNotFoundException
     * @throws PaymentException
     * @return bool
     */
    protected function tryPaymentRefund(Order $order, int $amount): bool
    {
        $orderInvoiceId = $this->getOrderInvoiceId($order);
        return $this->response(function () use ($orderInvoiceId, $amount) {
            return $this->getSberbankProcessing()->refundPayment($orderInvoiceId, $amount);
        });
    }

    /**
     * @todo CopyPaste from Sberbank pay system.
     * Do refactor.
     *
     * @param callable $responseCallback
     *
     * @return bool
     *
     * @throws PaymentException
     */
    private function response(callable $responseCallback): bool
    {
        $response = ['Fake response'];

        for ($i = 0; $i <= 10; $i++) {
            $response = $responseCallback();

            if ((int)$response['errorCode'] !== 1) {
                break;
            }
        }

        return $this->getSberbankProcessing()->parseResponse($response);
    }

    /**
     * @return Sberbank
     */
    private function getSberbankProcessing(): Sberbank
    {
        if (null === $this->sberbankProcessing) {
            /** @noinspection PhpIncludeInspection */
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';
            $settings = BusinessValueHelper::getPaysystemSettings(3, ['USER_NAME', 'PASSWORD', 'TEST_MODE', 'TWO_STAGE', 'LOGGING']);

            $this->sberbankProcessing = new Sberbank(
                $settings['USER_NAME'],
                $settings['PASSWORD'],
                $settings['TWO_STAGE'] === 'Y',
                $settings['TEST_MODE'] === 'Y',
                $settings['LOGGING'] === 'Y'
            );
        }

        return $this->sberbankProcessing;
    }
}
