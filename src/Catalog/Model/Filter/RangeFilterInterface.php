<?php

namespace FourPaws\Catalog\Model\Filter;

interface RangeFilterInterface extends FilterInterface
{
    /**
     * Возвращает минимальное возможное значение.
     *
     * @return float
     */
    public function getMinValue(): float;

    /**
     * Возвращает максимальное возможное значение.
     *
     * @return float
     */
    public function getMaxValue(): float;

    /**
     * Возвращает текущее значение "от".
     *
     * @return float
     */
    public function getFromValue(): float;

    /**
     * Возвращает текущее значение "до".
     *
     * @return float
     */
    public function getToValue(): float;

}
