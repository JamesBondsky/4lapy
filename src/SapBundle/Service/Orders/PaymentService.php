<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Dto\Fiscalization\CartItems;
use FourPaws\SaleBundle\Dto\Fiscalization\Fiscalization;
use FourPaws\SaleBundle\Dto\Fiscalization\Item as FiscalItem;
use FourPaws\SaleBundle\Dto\Fiscalization\ItemQuantity;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\FiscalValidation\FiscalAmountExceededException;
use FourPaws\SaleBundle\Exception\FiscalValidation\FiscalAmountException;
use FourPaws\SaleBundle\Exception\FiscalValidation\InvalidItemCodeException;
use FourPaws\SaleBundle\Exception\FiscalValidation\NoMatchingFiscalItemException;
use FourPaws\SaleBundle\Exception\FiscalValidation\PositionQuantityExceededException;
use FourPaws\SaleBundle\Exception\FiscalValidation\PositionWrongAmountException;
use FourPaws\SaleBundle\Exception\PaymentException as SalePaymentException;
use FourPaws\SaleBundle\Exception\SberbankOrderNotFoundException;
use FourPaws\SaleBundle\Service\OrderService as SaleOrderService;
use FourPaws\SaleBundle\Service\PaymentService as SalePaymentService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Item;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Dto\Out\Payment\Debit as OutDebit;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\Payment\NotFoundInvoiceException;
use FourPaws\SapBundle\Exception\Payment\NotFoundOrderException;
use FourPaws\SapBundle\Exception\Payment\InvalidOrderNumberException;
use FourPaws\SapBundle\Exception\Payment\OrderZeroPriceException;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PaymentService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class PaymentService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait, SapOutFile;

    private const MODULE_PROVIDER_CODE = 'sberbank.ecom';
    private const OPTION_FISCALIZATION_CODE = 'FISCALIZATION';
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var string
     */
    private $outPath;
    /**
     * @var string
     */
    private $outPrefix;
    /**
     * @var SalePaymentService
     */
    private $salePaymentService;

    /**
     * PaymentService constructor.
     *
     * @param SaleOrderService    $saleOrderService
     * @param OrderService        $orderService
     * @param SerializerInterface $serializer
     * @param Filesystem          $filesystem
     * @param SalePaymentService  $salePaymentService
     * @param UserService         $userService
     */
    public function __construct(
        SaleOrderService $saleOrderService,
        OrderService $orderService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        SalePaymentService $salePaymentService,
        UserService $userService
    )
    {
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->salePaymentService = $salePaymentService;
        $this->setFilesystem($filesystem);
    }

    /**
     * @param Order $paymentTask
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws IOException
     * @throws InvalidOrderNumberException
     * @throws InvalidPathException
     * @throws NotFoundInvoiceException
     * @throws NotFoundOrderException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderZeroPriceException
     * @throws SalePaymentException
     * @throws SberbankOrderNotFoundException
     * @throws SystemException
     * @throws \Exception
     * @throws FiscalAmountExceededException
     * @throws FiscalAmountException
     * @throws InvalidItemCodeException
     * @throws NoMatchingFiscalItemException
     * @throws PositionQuantityExceededException
     * @throws PositionWrongAmountException
     */
    public function paymentTaskPerform(Order $paymentTask)
    {
        /**
         * Check order existence
         */
        if (!$paymentTask->getBitrixOrderId()) {
            throw new InvalidOrderNumberException('Order number is empty');
        }
        $order = SaleOrder::loadByAccountNumber($paymentTask->getBitrixOrderId());
        if (!$order) {
            throw new NotFoundOrderException(
                sprintf('Order with number %s not found', $paymentTask->getBitrixOrderId())
            );
        }

        if (!$paymentTask->getSumPayed() && !$paymentTask->getSumTotal()) {
            throw new OrderZeroPriceException('Сумма на списание и сумма заказа равны нулю');
        }

        if (!$orderInvoiceId = $this->salePaymentService->getOrderInvoiceId($order)) {
            throw new NotFoundInvoiceException('У заказа не указан номер инвойса');
        }

        $orderInfo = $this->salePaymentService->getSberbankOrderStatusByOrderId($orderInvoiceId);
        if ($fiscalization = $this->getFiscalization($order, $paymentTask)) {
            $this->salePaymentService->validateFiscalization(
                $fiscalization,
                $orderInfo,
                $paymentTask->getSumPayed() * 100
            );
        }

        $amount = $paymentTask->getSumPayed();
        $return = $paymentTask->getSumReturned();

        if ($amount) {
            $fiscal = [];
            if ($fiscalization) {
                $fiscal = $this->salePaymentService->fiscalToArray($fiscalization);
                $amount = $this->salePaymentService->getFiscalTotal($fiscalization);
            }

            $this->salePaymentService->depositPayment($order, $amount, $fiscal);

            $debit = (new OutDebit())
                ->setPayMerchantCode(SapOrder::ORDER_PAYMENT_ONLINE_MERCHANT_ID)
                ->setSapOrderId($paymentTask->getSapOrderId())
                ->setBitrixOrderId($paymentTask->getBitrixOrderId())
                ->setClientId($order->getUserId())
                ->setClientFio($this->orderService->getPropertyValueByCode($order, 'NAME'))
                ->setClientPhone($this->orderService->getPropertyValueByCode($order, 'PHONE'))
                ->setClientAddress($this->orderService->getDeliveryAddress($order)->__toString())
                ->setPayHoldTransaction($orderInvoiceId)
                ->setPayStatus(SapOrder::ORDER_PAYMENT_STATUS_PAYED);
            $deliveryDate = \DateTime::createFromFormat(
                'd.m.Y',
                $this->orderService->getPropertyValueByCode($order, 'DELIVERY_DATE')
            );
            if ($deliveryDate instanceof \DateTime) {
                $debit->setDeliveryDate($deliveryDate);
            }

            $datePaid = null;
            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                if ($payment->isInner()) {
                    continue;
                }

                $datePaid = $payment->getField('DATE_PAID');
            }

            if ($datePaid instanceof DateTime) {
                $debit->setPaymentDate(DateHelper::convertToDateTime($datePaid));
            }

            $this->out($debit);
        } elseif ($return) {
            $this->salePaymentService->cancelPayment($order);
        }
    }

    /**
     * @param OutDebit $debit
     *
     * @throws IOException
     */
    public function out(OutDebit $debit)
    {
        $xml = $this->serializer->serialize($debit, 'xml');

        $this->filesystem->dumpFile($this->getFileName($debit), $xml);
    }

    /**
     * @param OutDebit $debit
     *
     * @return string
     */
    public function getFileName($debit): string
    {
        return \sprintf(
            '/%s/%s%s.xml',
            \trim($this->outPath, '/'),
            $this->outPrefix,
            $debit->getBitrixOrderId()
        );
    }

    /**
     * @param SaleOrder $order
     * @param Order     $paymentTask
     *
     * @return Fiscalization|null
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws InvalidPathException
     * @throws ObjectPropertyException
     */
    private function getFiscalization(SaleOrder $order, Order $paymentTask): ?Fiscalization
    {
        $config = Option::get(self::MODULE_PROVIDER_CODE, self::OPTION_FISCALIZATION_CODE, []);
        /** @noinspection UnserializeExploitsInspection */
        $config = \unserialize($config, []);

        if ($config['ENABLE'] !== 'Y') {
            return null;
        }

        $newItemArr = [];

        $paymentTask->getItems()->map(function (Item $item) use (&$newItemArr) {
            $newItemArr[$item->getOfferXmlId()][] = $item;
        });

        $xmlIdsItems = array_keys($newItemArr);

        if ($xmlIdsItems) {
            $offers = (new OfferQuery())
                ->withFilter([
                    '=XML_ID' => $xmlIdsItems
                ])
                ->withSelect(['ID', 'XML_ID'])
                ->exec();
            foreach ($offers as $offer) {
                /** @var Offer $offer */
                $productIds[$offer->getXmlId()] = $offer->getId();
            }

            if (isset($productIds)) {
                $arMeasure = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($productIds);
                foreach ($arMeasure as $offerId => $offerUnit) {
                    $measureUnits[$offerId] = $offerUnit['MEASURE']['SYMBOL_RUS'];
                }
            }
        }

        $itemsOrder = [];

        if (count($newItemArr) > 0) {
            foreach ($xmlIdsItems as $xmlIdItem) {
                /** @var Item $newItem */
                $newItem = clone end($newItemArr[$xmlIdItem]);
                $newItem->setQuantity(0);
                $newItem->setSumPrice(0);

                /** @var Item $newItemOriginal */
                $newItemOriginal = clone $newItem;

                /** @var Item $item */
                foreach ($newItemArr[$xmlIdItem] as $item) {
                    $newItem->setQuantity(floatval($newItem->getQuantity()) + floatval($item->getQuantity()));
                    $newItem->setSumPrice(floatval($newItem->getSumPrice()) + floatval($item->getSumPrice()));
                }

                $averagePriceItem = $newItem->getSumPrice() / floatval($newItem->getQuantity());
                $wholeCnt = $this->checkWholeNumber($averagePriceItem);

                if ($wholeCnt > 2) {
                    $origSumAmount = $newItem->getSumPrice();
                    $newAveragePriceItem = $this->modifyNum($averagePriceItem, 2);
                    $newItem->setQuantity(floatval($newItem->getQuantity()) - 1);
                    $newItem->setSumPrice(floatval($newAveragePriceItem) * $newItem->getQuantity());
                    $newItem->setPrice($newAveragePriceItem);

                    if ($newItem->getPrice() > 0) {
                        $itemsOrder[$xmlIdItem][] = $newItem;
                    }

                    $newItemOriginal->setPrice($origSumAmount - $newItem->getSumPrice());
                    $newItemOriginal->setSumPrice($origSumAmount - $newItem->getSumPrice());
                    $newItemOriginal->setQuantity(1);

                    if ($newItemOriginal->getPrice() > 0) {
                        $itemsOrder[$xmlIdItem][] = $newItemOriginal;
                    }

                } else {
                    if ($newItem->getPrice() > 0) {
                        $itemsOrder[$xmlIdItem][] = $newItem;
                    }
                }
            }
        }

        $fiscalization = $this->salePaymentService->getFiscalization($order, (int)$config['TAX_SYSTEM']);

        $itemsInCart = $fiscalization->getFiscal()->getOrderBundle()->getCartItems()->getItems();

        asort($itemsOrder);

        $itemsFiscal = [];
        foreach ($itemsOrder as $xmlId => $ptItems) {
            foreach ($ptItems as $ptItem) {
                $tmpItem = new FiscalItem();
                $newQuantity = $ptItem->getQuantity();
                if ($newQuantity > 0) {
                    $itemQuantity = (new ItemQuantity())
                        ->setValue((int)$newQuantity);
                    if ($unit = $measureUnits[$productIds[$ptItem->getOfferXmlId()]]) {
                        $itemQuantity->setMeasure($unit);
                    } else {
                        $itemQuantity->setMeasure('шт');
                    }
                    $tmpItem->setQuantity($itemQuantity);
                    $tmpItem->setTotal(round($ptItem->getPrice() * $newQuantity * 100));
                    $tmpItem->setPrice(round($ptItem->getPrice() * 100));
                    $tmpItem->setName($ptItem->getOfferName());
                    $tmpItem->setXmlId($ptItem->getOfferXmlId());

                    $xmlId = $ptItem->getOfferXmlId();


                    $tmpFindItem = array_map(function ($itemCart) use ($xmlId) {
                        if ($itemCart->getXmlId() == $xmlId) {
                            return $itemCart;
                        }
                    }, $itemsInCart->toArray());

                    $tmpFindItem = array_filter($tmpFindItem, function ($tmpFindItemItem) {
                        if ($tmpFindItemItem) {
                            return true;
                        }
                    });

                    $tmpFindItem = array_shift($tmpFindItem);

                    if ($tmpFindItem) {
                        $tmpItem->setPositionId($tmpFindItem->getPositionId());

                        $tmpItem->setPaymentMethod($tmpFindItem->getPaymentMethod());
                        $tmpItem->setTax($tmpFindItem->getTax());
                        $tmpItem->setCode($tmpFindItem->getCode());

                        $itemsFiscal[] = $tmpItem;
                    }
                }
            }

        }

        $fiscalization->getFiscal()->getOrderBundle()->setCartItems((new CartItems())->setItems(new ArrayCollection($itemsFiscal)));

        return $fiscalization;
    }

    /**
     * Получение количества знаков после запятой
     * @param $number
     * @return int
     */
    private function checkWholeNumber($number): int
    {
        list ($averagePriceItemWhole, $averagePriceItemFractional) = explode('.', $number);

        return strlen($averagePriceItemFractional);
    }

    private function modifyNum($number, $count)
    {
        list ($averagePriceItemWhole, $averagePriceItemFractional) = explode('.', $number);

        if (strlen($averagePriceItemFractional) > $count) {
            $averagePriceItemFractional = substr($averagePriceItemFractional, 0, $count);
        }

        return floatval($averagePriceItemWhole . '.' . $averagePriceItemFractional);
    }

    /**
     * @param Item $item
     *
     * @return bool
     */
    private function isDeliveryItem(Item $item): bool {
        $deliveryArticles = [
            SapOrder::DELIVERY_ZONE_1_ARTICLE,
            SapOrder::DELIVERY_ZONE_2_ARTICLE,
            SapOrder::DELIVERY_ZONE_3_ARTICLE,
            SapOrder::DELIVERY_ZONE_4_ARTICLE,
            SapOrder::DELIVERY_ZONE_5_ARTICLE,
            SapOrder::DELIVERY_ZONE_6_ARTICLE,
            SapOrder::DELIVERY_ZONE_MOSCOW_ARTICLE,
        ];

        return \in_array((string)$item->getOfferXmlId(), $deliveryArticles, true);
    }
}
