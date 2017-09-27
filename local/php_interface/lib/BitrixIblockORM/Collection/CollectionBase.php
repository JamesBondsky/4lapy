<?php

namespace FourPaws\BitrixIblockORM\Collection;

use CDBResult;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\BitrixIblockORM\Model\BitrixArrayItemBase;

abstract class CollectionBase extends ArrayCollection
{
    /**
     * @var CDBResult
     */
    protected $CDBResult;

    /**
     * @var int Сколько всего элементов выбрано, если мы получили только одну страницу.
     */
    protected $totalCount = 0;

    public function __construct(CDBResult $CDBResult)
    {
        parent::__construct();

        $this->CDBResult = $CDBResult;
        $this->totalCount = (int)$this->getCDBResult()->AffectedRowsCount();

        if (true === $this->getCDBResult()->bFromArray) {
            foreach ($this->getCDBResult()->arResult as $key => $value) {
                $this->set($key, $value);
            }
            $this->totalCount = $this->count();
        } else {
            while ($item = $this->doFetch()) {
                $this->set($item->getId(), $item);
            }
        }
    }

    /**
     * Создание объекта
     *
     * @return BitrixArrayItemBase
     */
    abstract protected function doFetch();

    /**
     * @return CDBResult
     */
    public function getCDBResult()
    {
        return $this->CDBResult;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
