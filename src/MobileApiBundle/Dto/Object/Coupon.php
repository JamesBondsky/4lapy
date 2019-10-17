<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектКупона
 * Class Coupon
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class Coupon
{
    /**
     * Не доступен
     *
     * @var int
     */
    const NOT_AVAILABLE   = 0;
    
    /**
     * Применить
     *
     * @var int
     */
    const ENABLE   = 1;
    
    /**
     * Отменить
     *
     * @var int
     */
    const DISABLE   = 2;
    
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id = '';
    
    /**
     * @Serializer\SerializedName("promocode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $promocode = '';
    
    /**
     * @Serializer\SerializedName("discount")
     * @Serializer\Type("string")
     * @var string
     */
    protected $discount = '';
    
    /**
     * @Serializer\SerializedName("text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $text = '';
    
    /**
     * @Serializer\SerializedName("date_active")
     * @Serializer\Type("string")
     * @var string
     */
    protected $dateActive = '';
    
    /**
     * @Serializer\SerializedName("actionType")
     * @Serializer\Type("int")
     * @var int
     */
    protected $actionType = 0;
    
    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * @param string $id
     *
     * @return Coupon
     */
    public function setId(string $id): Coupon
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPromocode(): string
    {
        return $this->promocode;
    }
    
    /**
     * @param string $promocode
     *
     * @return Coupon
     */
    public function setPromocode(string $promocode): Coupon
    {
        $this->promocode = $promocode;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDiscount(): string
    {
        return $this->discount;
    }
    
    /**
     * @param string $discount
     *
     * @return Coupon
     */
    public function setDiscount(string $discount): Coupon
    {
        $this->discount = $discount;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
    
    /**
     * @param string $text
     *
     * @return Coupon
     */
    public function setText(string $text): Coupon
    {
        $this->text = $text;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDateActive(): string
    {
        return $this->text;
    }
    
    /**
     * @param string $dateActive
     *
     * @return Coupon
     */
    public function setDateActive(string $dateActive): Coupon
    {
        $this->dateActive = $dateActive;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getActionType(): int
    {
        return $this->actionType;
    }
    
    /**
     * @param int $actionType
     *
     * @return Coupon
     */
    public function setActionType(int $actionType): Coupon
    {
        $this->actionType = $actionType;
        return $this;
    }
}
