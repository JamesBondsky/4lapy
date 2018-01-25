<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектЗаказ
 * Class Order
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class Order
{
    /**
     * ОбъектПараметрЗаказа
     * @Serializer\SerializedName("cart_param")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderParameter")
     * @var OrderParameter
     */
    protected $cartParam;

    /**
     * ОбъектРасчетЗаказа
     * @Serializer\SerializedName("cart_calc")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderCalculate")
     * @var OrderCalculate
     */
    protected $cartCalc;

    /**
     * Дата и время формирования
     * @Serializer\Exclude()
     * @var \DateTime
     */
    protected $dateFormat;

    /**
     * @internal
     * @Serializer\Accessor(setter="setDate", getter="getDate")
     * @Serializer\SerializedName("date")
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @var string
     */
    protected $date = '';

    /**
     * @internal
     * @Serializer\Accessor(setter="setTime", getter="getTime")
     * @Serializer\SerializedName("time")
     * @Serializer\Type("string")
     * @var string
     */
    protected $time = '';

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderStatus")
     * @Serializer\SerializedName("status")
     * @var OrderStatus
     */
    protected $status;

    /**
     * Признак того, что можно отправить отзыв.
     * @Serializer\SerializedName("review_enabled")
     * @Serializer\Type("boolean")
     * @var bool
     */
    protected $reviewEnabled = false;

    /**
     * Признак того, что заказ уже завершён
     * @Serializer\SerializedName("completed")
     * @Serializer\Type("boolean")
     * @var bool
     */
    protected $completed = false;

    /**
     * Признак того, что заказ уже оплачен
     * @Serializer\SerializedName("paid")
     * @Serializer\Type("boolean")
     * @var bool
     */
    protected $paid = false;

    /**
     * Ссылка на эл.чек.
     * @Serializer\SerializedName("recipe_url")
     * @Serializer\Type("string")
     * @var string
     */
    protected $recipeUrl = '';

    public function __construct()
    {
        $this->dateFormat = new \DateTIme();
    }

    /**
     * @return OrderParameter
     */
    public function getCartParam(): OrderParameter
    {
        return $this->cartParam;
    }

    /**
     * @param OrderParameter $cartParam
     *
     * @return Order
     */
    public function setCartParam(OrderParameter $cartParam): Order
    {
        $this->cartParam = $cartParam;
        return $this;
    }

    /**
     * @return OrderCalculate
     */
    public function getCartCalc(): OrderCalculate
    {
        return $this->cartCalc;
    }

    /**
     * @param OrderCalculate $cartCalc
     *
     * @return Order
     */
    public function setCartCalc(OrderCalculate $cartCalc): Order
    {
        $this->cartCalc = $cartCalc;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFormat(): \DateTime
    {
        return $this->dateFormat;
    }

    /**
     * @param \DateTime $dateFormat
     *
     * @return Order
     */
    public function setDateFormat(\DateTime $dateFormat): Order
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @return OrderStatus
     */
    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @param OrderStatus $status
     *
     * @return Order
     */
    public function setStatus(OrderStatus $status): Order
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReviewEnabled(): bool
    {
        return $this->reviewEnabled;
    }

    /**
     * @param bool $reviewEnabled
     *
     * @return Order
     */
    public function setReviewEnabled(bool $reviewEnabled): Order
    {
        $this->reviewEnabled = $reviewEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     *
     * @return Order
     */
    public function setCompleted(bool $completed): Order
    {
        $this->completed = $completed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @param bool $paid
     *
     * @return Order
     */
    public function setPaid(bool $paid): Order
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipeUrl(): string
    {
        return $this->recipeUrl;
    }

    /**
     * @param string $recipeUrl
     *
     * @return Order
     */
    public function setRecipeUrl(string $recipeUrl): Order
    {
        $this->recipeUrl = $recipeUrl;
        return $this;
    }

    /**
     * @internal
     * @return string
     */
    public function getTime()
    {
        return $this->dateFormat ? $this->dateFormat->format('H:i') : '';
    }

    /**
     * @internal
     *
     * @param string $time
     *
     * @return Order
     */
    public function setTime(string $time = '00:00')
    {
        $this->dateFormat = $this->dateFormat instanceof \DateTime ? $this->dateFormat : new \DateTime();
        $this->dateFormat->setTime(... explode(':', $time));
        return $this;
    }

    /**
     * @internal
     * @return \DateTime|string
     */
    public function getDate()
    {
        return $this->dateFormat ?? '';
    }

    /**
     * @internal
     *
     * @param \DateTime $date
     *
     * @return Order
     */
    public function setDate(\DateTime $date)
    {
        $this->dateFormat = $this->dateFormat instanceof \DateTime ? $this->dateFormat : $date;
        $this->dateFormat->setDate(
            $date->format('Y'),
            $date->format('n'),
            $date->format('j')
        );
        return $this;
    }
}
