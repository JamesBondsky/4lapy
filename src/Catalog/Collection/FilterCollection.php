<?php

namespace FourPaws\Catalog\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Filter\InternalFilter;
use InvalidArgumentException;

class FilterCollection extends ObjectArrayCollection
{

    /**
     * @param mixed $filter
     */
    protected function checkType($filter)
    {
        if (!($filter instanceof FilterInterface)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . FilterInterface::class);
        }
    }

    /**
     * Возвращает bool true, если хотя бы один фильтр выбран без учёта фильтрации по категории.
     *
     * @return bool
     */
    public function hasCheckedFilter(): bool
    {
        /** @var FilterInterface $filter */
        foreach ($this as $filter) {
            //Категория особый фильтр, который, кроме особых случаев, выбран.
            if (
                !($filter instanceof Category)
                && !($filter instanceof InternalFilter)
                && $filter->hasCheckedVariants()
            ) {
                return true;
            }
        }

        return false;
    }

}
