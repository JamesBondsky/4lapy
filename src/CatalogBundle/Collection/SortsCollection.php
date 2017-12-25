<?php

namespace FourPaws\CatalogBundle\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Exception\RuntimeException;
use InvalidArgumentException;

class SortsCollection extends ObjectArrayCollection
{
    public function __construct(array $objects = [], string $activeSort = '')
    {
        parent::__construct($objects);
        $this->setSelected($activeSort);
    }

    /**
     * @param string $activeSort
     *
     * @return static
     */
    public function setSelected(string $activeSort)
    {
        /**
         * @var Sorting $sort
         * @var Sorting $first
         */
        if ($activeSort) {
            foreach ($this->getIterator() as $sort) {
                if ($sort->getValue() === $activeSort) {
                    $sort->withSelected(true);
                    return $this;
                }
            }
        }
        $first = $this->first();
        $first->withSelected(true);
        return $this;
    }

    /**
     * @throws RuntimeException
     * @return Sorting
     */
    public function getSelected(): Sorting
    {
        $selectedSorting = $this->filter(function (Sorting $sorting) {
            return $sorting->isSelected();
        })->first();

        if (!($selectedSorting instanceof Sorting)) {
            throw new RuntimeException('Не удалось обнаружить выбранную сортировку');
        }

        return $selectedSorting;
    }

    /**
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function checkType($object)
    {
        if (!($object instanceof Sorting)) {
            throw new InvalidArgumentException('Попытка добавить не сортировку в коллекцию сортировок');
        }
    }
}
