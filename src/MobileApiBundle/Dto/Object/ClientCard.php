<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * КартаКлиента
 * Class ClientCard
 *
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class ClientCard
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("balance")
     * @var float
     */
    protected $balance = 0;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("number")
     * @var string
     */
    protected $number = '';

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("sale_amount")
     * @var float
     */
    protected $saleAmount = 0;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ClientCard
     */
    public function setTitle(string $title): ClientCard
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param string $balance
     *
     * @return ClientCard
     */
    public function setBalance(string $balance): ClientCard
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return ClientCard
     */
    public function setNumber(string $number): ClientCard
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return float
     */
    public function getSaleAmount(): float
    {
        return $this->saleAmount;
    }

    /**
     * @param float $saleAmount
     *
     * @return ClientCard
     */
    public function setSaleAmount(float $saleAmount): ClientCard
    {
        $this->saleAmount = $saleAmount;
        return $this;
    }
}
