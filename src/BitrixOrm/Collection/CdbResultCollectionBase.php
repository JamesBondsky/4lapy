<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\BitrixArrayItemBase;

abstract class CdbResultCollectionBase extends CollectionBase
{
    /**
     * @var \CDBResult
     */
    protected $cdbResult;

    /**
     * @var int Сколько всего элементов выбрано, если мы получили только одну страницу.
     */
    protected $totalCount = 0;

    public function __construct(\CDBResult $result)
    {
        $this->cdbResult = $result;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    protected function doInitialize()
    {
        if (true === $this->getCdbResult()->bFromArray && \is_array($this->getCdbResult()->arResult)) {
            /**
             * @todo Зачем тут инициилизация из BitrixItemBase
             * @todo Лучше залогировать и удалить
             */
            $result = (array)$this->getCdbResult()->arResult;

            foreach ($result as $key => $value) {
                if ($value instanceof BitrixArrayItemBase) {
                    $this->set($value->getId(), $value);
                } elseif (\is_array($value) && array_key_exists('ID', $value)) {
                    $this->set($value['ID'], $value);
                } else {
                    $this->set($key, $value);
                }
            }
            $this->totalCount = $this->count();
        } else {
            parent::doInitialize();
            $this->totalCount = (int)$this->cdbResult->AffectedRowsCount();
        }
    }

    /**
     * @return \CDBResult
     */
    public function getCdbResult(): \CDBResult
    {
        return $this->cdbResult;
    }
}
