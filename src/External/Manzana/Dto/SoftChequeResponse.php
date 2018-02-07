<?php

namespace FourPaws\External\Manzana\Dto;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SoftChequeResponse
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ChequeRequest")
 * @Serializer\XmlNamespace(uri="http://loyalty.manzanagroup.ru/loyalty.xsd")
 */
class SoftChequeResponse
{
    /**
     * УИД торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ChequeType")
     *
     * @var string
     */
    protected $chequeType = 'Soft';
    
    /**
     * Идентификатор запроса
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("RequestId")
     *
     * @var string
     */
    protected $requestId = '';
    
    /**
     * Дата и время совершения операции
     * Дата не может быть больше текущей даты системы Manzana Loyalty
     *
     * @Serializer\Type("DateTimeImmutable")
     * @Serializer\SerializedName("DateTime")
     *
     * @var \DateTimeImmutable
     */
    protected $datetime;
    
    /**
     * Код Партнера
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Organization")
     *
     * @var string
     */
    protected $organization = '';
    
    /**
     * Код Магазина
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BusinessUnit")
     *
     * @var string
     */
    protected $businessUnit = '';
    
    /**
     * Код POS терминала
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("POS")
     *
     * @var string
     */
    protected $pos = '';
    
    /**
     * Номер карты
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CardNumber")
     *
     * @var string
     */
    protected $cardNumber = '';
    
    /**
     * Номер чека
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Number")
     *
     * @var string
     */
    protected $number = '';
    
    /**
     * Тип операции
     * Всегда значене sale для данного БП
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("OperationType")
     *
     * @var string
     */
    protected $operationType = 'sale';
    
    /**
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\ChequePosition>")
     *
     * @var Collection|ChequePosition[]
     */
    protected $items;
    
    /**
     * @Serializer\Type("FourPaws\External\Manzana\Dto\Coupons")
     *
     * @var Coupons
     */
    protected $coupons;
    
    /**
     * Сумма.
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;
    
    /**
     * Скидка, %
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = 0;
    
    /**
     * Сумма с учетом скидки
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = 0;
    
    /**
     * Оплачено бонусами
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PaidByBonus")
     *
     * @var float
     */
    protected $paidByBonus = 0;
    
}
