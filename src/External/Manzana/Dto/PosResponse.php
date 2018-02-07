<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class PosResponse
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ChequeResponse")
 */
class PosResponse
{
    /**
     * Идентификатор транзакции
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("TransactionID")
     *
     * @var string
     */
    protected $transactionId = 0;
    
    /**
     * Идентификатор запроса
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("RequestID")
     *
     * @var string
     */
    protected $requestId = '';
    
    /**
     * Дата и время операции (в системе)
     * Используется локальное время системы, обрабатывающей мягкий чек (московское).
     *
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
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ReturnCode")
     *
     * @var int
     */
    protected $returnCode = 0;
    
    /**
     * Текстовое описание ошибки
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Message")
     *
     * @var string
     */
    protected $message = '';
    
    /**
     * Сумма без скидки по чеку, деньги
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;
    
    /**
     * Скидка по чеку, %
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = '';
    
    /**
     * Сумма со скидкой по чеку, деньги
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = '';
    
    /**
     * Сумма начисленного бонуса
     *
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
}
