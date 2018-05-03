<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\StoreBundle\Entity\Store;

class Terminal extends Store
{
    /**
     * @var bool
     */
    protected $nppAvailable = false;

    /**
     * @var int
     */
    protected $nppValue = 0;

    /**
     * @var bool
     */
    protected $cardPayment = false;

    /**
     * @var bool
     */
    protected $cashPayment = false;

    /**
     * @return bool
     */
    public function isNppAvailable(): bool
    {
        return $this->nppAvailable;
    }

    /**
     * @param bool $nppAvailable
     *
     * @return Terminal
     */
    public function setNppAvailable(bool $nppAvailable): Terminal
    {
        $this->nppAvailable = $nppAvailable;
        return $this;
    }

    /**
     * @return int
     */
    public function getNppValue(): int
    {
        return $this->nppValue;
    }

    /**
     * @param int $nppValue
     *
     * @return Terminal
     */
    public function setNppValue(int $nppValue): Terminal
    {
        $this->nppValue = $nppValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCardPayment(): bool
    {
        return $this->cardPayment;
    }

    /**
     * @param bool $cardPayment
     *
     * @return Terminal
     */
    public function setCardPayment(bool $cardPayment): Terminal
    {
        $this->cardPayment = $cardPayment;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCashPayment(): bool
    {
        return $this->cashPayment;
    }

    /**
     * @param bool $cashPayment
     *
     * @return Terminal
     */
    public function setCashPayment(bool $cashPayment): Terminal
    {
        $this->cashPayment = $cashPayment;
        return $this;
    }
}
