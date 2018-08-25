<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\OrderBundle;
use JMS\Serializer\Annotation as Serializer;

class OrderInfo
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("errorCode")
     * @Serializer\Type("int")
     */
    protected $errorCode = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("errorMessage")
     * @Serializer\Type("string")
     */
    protected $errorMessage = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("orderNumber")
     * @Serializer\Type("string")
     */
    protected $orderNumber = '';

    /**
     * @var int
     *
     * @Serializer\SerializedName("orderStatus")
     * @Serializer\Type("int")
     */
    protected $orderStatus = 0;

    /**
     * @var int
     *
     * @Serializer\SerializedName("orderStatus")
     * @Serializer\Type("int")
     */
    protected $actionCode = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("actionDescription")
     * @Serializer\Type("string")
     */
    protected $actionDescription;

    /**
     * @var int
     *
     * @Serializer\SerializedName("amount")
     * @Serializer\Type("int")
     */
    protected $amount = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("currency")
     * @Serializer\Type("string")
     */
    protected $currency;

    /**
     * @var int
     *
     * @Serializer\SerializedName("date")
     * @Serializer\Type("int")
     */
    protected $date;

    /**
     * @var string
     *
     * @Serializer\SerializedName("ip")
     * @Serializer\Type("string")
     */
    protected $ip;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("merchantOrderParams")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\SberbankOrderInfo\MerchantOrderParameter>")
     */
    protected $merchantOrderParams;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("attributes")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\SberbankOrderInfo\Attribute>")
     */
    protected $attributes;

    /**
     * @var CardAuthInfo
     *
     * @Serializer\SerializedName("cardAuthInfo")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\CardAuthInfo")
     */
    protected $cardAuthInfo;

    /**
     * @var int
     *
     * @Serializer\SerializedName("authDateTime")
     * @Serializer\Type("int")
     */
    protected $authDateTime;

    /**
     * @var string
     *
     * @Serializer\SerializedName("terminalId")
     * @Serializer\Type("string")
     */
    protected $terminalId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("authRefNum")
     * @Serializer\Type("string")
     */
    protected $authRefNum;

    /**
     * @var PaymentAmountInfo
     *
     * @Serializer\SerializedName("paymentAmountInfo")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\PaymentAmountInfo")
     */
    protected $paymentAmountInfo;

    /**
     * @var BankInfo
     *
     * @Serializer\SerializedName("bankInfo")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\BankInfo")
     */
    protected $bankInfo;

    /**
     * @var OrderBundle
     *
     * @Serializer\SerializedName("orderBundle")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\OrderBundle")
     */
    protected $orderBundle;

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     * @return OrderInfo
     */
    public function setErrorCode(int $errorCode): OrderInfo
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     * @return OrderInfo
     */
    public function setErrorMessage(string $errorMessage): OrderInfo
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     * @return OrderInfo
     */
    public function setOrderNumber(string $orderNumber): OrderInfo
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderStatus(): int
    {
        return $this->orderStatus;
    }

    /**
     * @param int $orderStatus
     * @return OrderInfo
     */
    public function setOrderStatus(int $orderStatus): OrderInfo
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getActionCode(): int
    {
        return $this->actionCode;
    }

    /**
     * @param int $actionCode
     * @return OrderInfo
     */
    public function setActionCode(int $actionCode): OrderInfo
    {
        $this->actionCode = $actionCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionDescription(): string
    {
        return $this->actionDescription;
    }

    /**
     * @param string $actionDescription
     * @return OrderInfo
     */
    public function setActionDescription(string $actionDescription): OrderInfo
    {
        $this->actionDescription = $actionDescription;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return OrderInfo
     */
    public function setAmount(int $amount): OrderInfo
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return OrderInfo
     */
    public function setCurrency(string $currency): OrderInfo
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return int
     */
    public function getDate(): int
    {
        return $this->date;
    }

    /**
     * @param int $date
     * @return OrderInfo
     */
    public function setDate(int $date): OrderInfo
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return OrderInfo
     */
    public function setIp(string $ip): OrderInfo
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMerchantOrderParams(): ArrayCollection
    {
        return $this->merchantOrderParams;
    }

    /**
     * @param ArrayCollection $merchantOrderParams
     * @return OrderInfo
     */
    public function setMerchantOrderParams(ArrayCollection $merchantOrderParams): OrderInfo
    {
        $this->merchantOrderParams = $merchantOrderParams;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection $attributes
     * @return OrderInfo
     */
    public function setAttributes(ArrayCollection $attributes): OrderInfo
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return CardAuthInfo
     */
    public function getCardAuthInfo(): CardAuthInfo
    {
        return $this->cardAuthInfo;
    }

    /**
     * @param CardAuthInfo $cardAuthInfo
     * @return OrderInfo
     */
    public function setCardAuthInfo(CardAuthInfo $cardAuthInfo): OrderInfo
    {
        $this->cardAuthInfo = $cardAuthInfo;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthDateTime(): int
    {
        return $this->authDateTime;
    }

    /**
     * @param int $authDateTime
     * @return OrderInfo
     */
    public function setAuthDateTime(int $authDateTime): OrderInfo
    {
        $this->authDateTime = $authDateTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    /**
     * @param string $terminalId
     * @return OrderInfo
     */
    public function setTerminalId(string $terminalId): OrderInfo
    {
        $this->terminalId = $terminalId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthRefNum(): string
    {
        return $this->authRefNum;
    }

    /**
     * @param string $authRefNum
     * @return OrderInfo
     */
    public function setAuthRefNum(string $authRefNum): OrderInfo
    {
        $this->authRefNum = $authRefNum;

        return $this;
    }

    /**
     * @return PaymentAmountInfo
     */
    public function getPaymentAmountInfo(): PaymentAmountInfo
    {
        return $this->paymentAmountInfo;
    }

    /**
     * @param PaymentAmountInfo $paymentAmountInfo
     * @return OrderInfo
     */
    public function setPaymentAmountInfo(PaymentAmountInfo $paymentAmountInfo): OrderInfo
    {
        $this->paymentAmountInfo = $paymentAmountInfo;

        return $this;
    }

    /**
     * @return BankInfo
     */
    public function getBankInfo(): BankInfo
    {
        return $this->bankInfo;
    }

    /**
     * @param BankInfo $bankInfo
     * @return OrderInfo
     */
    public function setBankInfo(BankInfo $bankInfo): OrderInfo
    {
        $this->bankInfo = $bankInfo;

        return $this;
    }

    /**
     * @return OrderBundle
     */
    public function getOrderBundle(): OrderBundle
    {
        return $this->orderBundle;
    }

    /**
     * @param OrderBundle $orderBundle
     * @return OrderInfo
     */
    public function setOrderBundle(OrderBundle $orderBundle): OrderInfo
    {
        $this->orderBundle = $orderBundle;

        return $this;
    }
}
