<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Cheque
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("Cheque")
 */
class Cheque
{
    /**
     * Идентификатор чека в кассовой системе
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("cheque_id")
     *
     * @var integer
     */
    protected $chequeId = 0;
    
    /**
     * Номер чека
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("cheque_number")
     *
     * @var string
     */
    protected $chequeNumber = '';
    
    /**
     * Номер карты
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("card_number")
     *
     * @var string
     */
    protected $cardNumber = '';
    
    /**
     * Дата и время совершения операции
     * Дата не может быть больше текущей даты системы Manzana Loyalty
     *
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("datetime")
     *
     * @var \DateTimeImmutable
     */
    protected $datetime;
    
    /**
     * Код Партнера
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("organization_id")
     *
     * @var string
     */
    protected $organizationId = '';
    
    /**
     * Код Магазина
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("businessunit_id")
     *
     * @var string
     */
    protected $businessUnitId = '';
    
    /**
     * Код POS терминала
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("pos_id")
     *
     * @var string
     */
    protected $posId = '';
    
    /**
     * Флаг, указывает на наличие позиций у чека:
     * - 0 – позиций нет;
     * - 1 – позиции есть.
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("has_position")
     *
     * @var integer
     */
    protected $hasPosition = 0;
    
    /**
     * Тип операции:
     *
     * - 1 – покупка
     * - 2 – возврат
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("operation_type")
     *
     * @var integer
     */
    protected $operationType = 1;
    
    /**
     * Сумма чека.
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;
    
    /**
     * Сумма чека с учетом скидки
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = '';
    
    /**
     * Скидка, %
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = '';
}
