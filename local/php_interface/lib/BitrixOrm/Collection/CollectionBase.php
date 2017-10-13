<?php

namespace FourPaws\BitrixOrm\Collection;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class CollectionBase
 *
 * @package FourPaws\BitrixOrm\Collection
 */
abstract class CollectionBase extends ArrayCollection
{
    /**
     * @var int Сколько всего элементов выбрано, если мы получили только одну страницу.
     */
    protected $totalCount = 0;
    
    /**
     * Извлечение модели
     */
    abstract protected function fetchElement() : \Generator;
    
    /**
     * Заполнение коллекции объектами
     */
    protected function populateCollection()
    {
        foreach ($this->fetchElement() as $element) {
            /**
             * @var \FourPaws\BitrixOrm\Model\BitrixArrayItemBase
             */
            $this->set($element->getId(), $element);
        }
    }
    
    /**
     * @return int
     */
    public function getTotalCount() : int
    {
        return $this->totalCount;
    }
}
