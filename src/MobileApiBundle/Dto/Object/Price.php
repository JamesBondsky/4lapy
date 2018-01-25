<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

class Price
{
    /**
     * @var double
     */
    protected $actual = 0.0;

    /**
     * @todo if old > actual показывать пустую строку
     * @var double
     */
    protected $old = 0.0;

    /**
     * @return float
     */
    public function getActual(): float
    {
        return $this->actual;
    }

    /**
     * @param float $actual
     *
     * @return Price
     */
    public function setActual(float $actual): Price
    {
        $this->actual = $actual;
        return $this;
    }

    /**
     * @return float
     */
    public function getOld(): float
    {
        return $this->old;
    }

    /**
     * @param float $old
     *
     * @return Price
     */
    public function setOld(float $old): Price
    {
        $this->old = $old;
        return $this;
    }
}
