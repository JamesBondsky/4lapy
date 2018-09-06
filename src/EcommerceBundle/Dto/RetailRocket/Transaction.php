<?php

namespace FourPaws\EcommerceBundle\Dto\RetailRocket;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Transaction
 *
 * @package FourPaws\EcommerceBundle\Dto\RetailRocket
 */
class Transaction
{
    /**
     * Id транзакции
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $transaction;

    /**
     * @Serializer\Type("ArrayCollection<FourPaws\EcommerceBundle\Dto\RetailRocket\Item>")
     *
     * @var ArrayCollection|Item[]
     */
    protected $items;

    /**
     * @return ArrayCollection|Product[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection|Item[] $items
     *
     * @return Transaction
     */
    public function setItems($items): Transaction
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransaction(): string
    {
        return $this->transaction;
    }

    /**
     * @param string $transaction
     *
     * @return Transaction
     */
    public function setTransaction(string $transaction): Transaction
    {
        $this->transaction = $transaction;

        return $this;
    }
}
