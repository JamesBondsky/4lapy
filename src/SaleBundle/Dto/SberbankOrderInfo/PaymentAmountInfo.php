<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo;

use JMS\Serializer\Annotation as Serializer;

class PaymentAmountInfo
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("paymentState")
     * @Serializer\Type("string")
     */
    protected $paymentState = '';

    /**
     * @var int
     *
     * @Serializer\SerializedName("approvedAmount")
     * @Serializer\Type("int")
     */
    protected $approvedAmount = 0;

    /**
     * @var int
     *
     * @Serializer\SerializedName("depositedAmount")
     * @Serializer\Type("int")
     */
    protected $depositedAmount = 0;

    /**
     * @var int
     *
     * @Serializer\SerializedName("refundedAmount")
     * @Serializer\Type("int")
     */
    protected $refundedAmount = 0;

    /**
     * @return string
     */
    public function getPaymentState(): string
    {
        return $this->paymentState;
    }

    /**
     * @param string $paymentState
     * @return PaymentAmountInfo
     */
    public function setPaymentState(string $paymentState): PaymentAmountInfo
    {
        $this->paymentState = $paymentState;

        return $this;
    }

    /**
     * @return int
     */
    public function getApprovedAmount(): int
    {
        return $this->approvedAmount;
    }

    /**
     * @param int $approvedAmount
     * @return PaymentAmountInfo
     */
    public function setApprovedAmount(int $approvedAmount): PaymentAmountInfo
    {
        $this->approvedAmount = $approvedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getDepositedAmount(): int
    {
        return $this->depositedAmount;
    }

    /**
     * @param int $depositedAmount
     * @return PaymentAmountInfo
     */
    public function setDepositedAmount(int $depositedAmount): PaymentAmountInfo
    {
        $this->depositedAmount = $depositedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefundedAmount(): int
    {
        return $this->refundedAmount;
    }

    /**
     * @param int $refundedAmount
     * @return PaymentAmountInfo
     */
    public function setRefundedAmount(int $refundedAmount): PaymentAmountInfo
    {
        $this->refundedAmount = $refundedAmount;

        return $this;
    }
}
