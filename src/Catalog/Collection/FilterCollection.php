<?php

namespace FourPaws\Catalog\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Filter\InternalFilter;
use FourPaws\Catalog\Model\Variant;
use InvalidArgumentException;

class FilterCollection extends ObjectArrayCollection
{

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

    /**
     * @return array
     */
    public function getCheckedFilterUrlParams(): array
    {
        $params = [];

        foreach ($this->getVisibleFilters() as $filter) {
            if ($filter->hasCheckedVariants()) {
                $params[$filter->getFilterCode()] = $filter
                    ->getCheckedVariants()
                    ->map(function (Variant $variant) {
                        return $variant->getValue();
                    })
                    ->toArray();
            }
        }
        return $params;
    }

    /**
     * @return ArrayCollection|Collection|FilterInterface[]
     */
    public function getVisibleFilters()
    {
        return $this->filter(function (FilterInterface $filterBase) {
            return $filterBase->isVisible();
        });
    }

    /**
     * @param mixed $filter
     */
    protected function checkType($filter)
    {
        if (!($filter instanceof FilterInterface)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . FilterInterface::class);
        }
    }
}
