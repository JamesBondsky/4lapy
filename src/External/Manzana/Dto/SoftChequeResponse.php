<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class SoftChequeResponse
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ChequeResponse")
 */
class SoftChequeResponse
{
    /**
     * Дата и время операции (в системе)
     * Используется локальное время системы, обрабатывающей мягкий чек (московское).
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("manzana_date_time_short")
     * @Serializer\SerializedName("Proccessed")
     *
     * @var \DateTimeImmutable
     */
    protected $proccessed;
    
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
     * Сумма без скидки по чеку, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;
    
    /**
     * Скидка по чеку, %
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = '';
    
    /**
     * Сумма со скидкой по чеку, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = '';
    
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
     * Сумма начисленного бонуса
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("ChargedBonus")
     *
     * @var float
     */
    protected $chargedBonus = '';
    
    /**
     * @return bool
     */
    public function isErrorResponse() : bool
    {
        return $this->returnCode !== 0;
    }
    
    /**
     * @return \DateTimeImmutable
     */
    public function getProccessed() : \DateTimeImmutable
    {
        return $this->proccessed;
    }
    
    /**
     * @return int
     */
    public function getReturnCode() : int
    {
        return $this->returnCode;
    }
    
    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }
    
    /**
     * @return float
     */
    public function getCardBalance() : float
    {
        return $this->cardBalance;
    }
    
    /**
     * @return float
     */
    public function getCardActiveBalance() : float
    {
        return $this->cardActiveBalance;
    }
    
    /**
     * @return float
     */
    public function getSumm() : float
    {
        return $this->summ;
    }
    
    /**
     * @return float
     */
    public function getDiscount() : float
    {
        return $this->discount;
    }
    
    /**
     * @return float
     */
    public function getSummDiscounted() : float
    {
        return $this->summDiscounted;
    }
    
    /**
     * @return float
     */
    public function getCardSumm() : float
    {
        return $this->cardSumm;
    }
    
    /**
     * @return float
     */
    public function getCardDiscount() : float
    {
        return $this->cardDiscount;
    }
    
    /**
     * @return float
     */
    public function getCardSummDiscounted() : float
    {
        return $this->cardSummDiscounted;
    }
    
    /**
     * @return float
     */
    public function getChargedBonus() : float
    {
        return $this->chargedBonus;
    }
}
