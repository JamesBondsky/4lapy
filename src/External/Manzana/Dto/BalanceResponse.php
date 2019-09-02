<?php

namespace FourPaws\External\Manzana\Dto;

use DateTimeImmutable;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BalanceResponse
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("BalanceResponse")
 */
class BalanceResponse
{
    /**
     * Дата и время операции (в системе)
     * Используется локальное время системы, обрабатывающей запрос (московское).
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("manzana_date_time_short")
     * @Serializer\SerializedName("Processed")
     *
     * @var DateTimeImmutable
     */
    protected $processed;

    /**
     * Код возврата
     * В случае ошибки отличен от нуля. Т.е. значение поля равное «ноль» означает, что ошибки не произошло.
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ReturnCode")
     *
     * @var int
     */
    protected $returnCode = 0;

    /**
     * Текстовое описание ошибки
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Message")
     *
     * @var string
     */
    protected $message = '';

    /**
     * Баланс карты, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardBalance")
     *
     * @var float
     */
    protected $cardBalance = 0;

    /**
     * Активный баланс карты, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardActiveBalance")
     *
     * @var float
     */
    protected $cardActiveBalance = 0;

    /**
     * Количество марок на карте
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardStatusBalance")
     *
     * @var float
     */
    protected $cardStatusBalance = 0;

    /**
     * Доступное для списания количество марок на карте
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardStatusActiveBalance")
     *
     * @var float
     */
    protected $cardStatusActiveBalance = 0;

    /**
     * Количество начисленных марок
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("ChargedStatusBonus")
     *
     * @var float
     */
    protected $chargedStatusBonus = 0;

    /**
     * Количество активируемых марок из начисленных
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("ActiveChargedStatusBonus")
     *
     * @var float
     */
    protected $activeChargedStatusBonus = 0;

    /**
     * ?, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardSumm")
     *
     * @var float
     */
    protected $cardSumm = 0;

    /**
     * ?, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardSummDiscounted")
     *
     * @var float
     */
    protected $cardSummDiscounted = '';

    /**
     * ?, %
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardDiscount")
     *
     * @var float
     */
    protected $cardDiscount = '';


    /**
     * @return bool
     */
    public function isErrorResponse(): bool
    {
        return $this->returnCode !== 0;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getProcessed(): DateTimeImmutable
    {
        return $this->processed;
    }

    /**
     * @return int
     */
    public function getReturnCode(): int
    {
        return (int)$this->returnCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return (string)$this->message;
    }

    /**
     * @return float
     */
    public function getCardBalance(): float
    {
        return (float)$this->cardBalance;
    }

    /**
     * @return float
     */
    public function getCardActiveBalance(): float
    {
        return (float)$this->cardActiveBalance;
    }

    /**
     * @return float
     */
    public function getCardStatusBalance(): float
    {
        return (float)$this->cardStatusBalance;
    }

    /**
     * @return int
     */
    public function getCardStatusActiveBalance(): int
    {
        return (int)$this->cardStatusActiveBalance;
    }

    /**
     * @return float
     */
    public function getCardSumm(): float
    {
        return (float)$this->cardSumm;
    }

    /**
     * @return float
     */
    public function getCardDiscount(): float
    {
        return (float)$this->cardDiscount;
    }

    /**
     * @return float
     */
    public function getCardSummDiscounted(): float
    {
        return (float)$this->cardSummDiscounted;
    }

    /**
     * @return float
     */
    public function getChargedStatusBonus(): float
    {
        return $this->chargedStatusBonus;
    }

    /**
     * @return float
     */
    public function getActiveChargedStatusBonus(): float
    {
        return $this->activeChargedStatusBonus;
    }
}
