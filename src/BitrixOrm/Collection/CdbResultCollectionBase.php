<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\BitrixArrayItemBase;

abstract class CdbResultCollectionBase extends CollectionBase
{
    protected $cdbResult;

    /**
     * @return \CDBResult
     */
    public function getCdbResult(): \CDBResult
    {
        return $this->cdbResult;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * CdbResultCollectionBase constructor.
     *
     * @param \CDBResult $cdbResult
     */
    public function __construct(\CDBResult $cdbResult)
    {
        parent::__construct();

        $this->cdbResult = $cdbResult;

        $this->populateCollection();
    }

    /**
     * PopulateCollection
     */
    protected function populateCollection()
    {
        if (true === $this->cdbResult->bFromArray) {
            foreach ($this->cdbResult->arResult as $key => $value) {

                if ($value instanceof BitrixArrayItemBase) {

                    $this->set($value->getId(), $value);

                } elseif (is_array($value) && array_key_exists('ID', $value)) {

                    $this->set($value['ID'], $value);

                } else {

                    $this->set($key, $value);

                }
            }

            $this->totalCount = $this->count();
        } else {
            parent::populateCollection();

            $this->totalCount = (int)$this->cdbResult->AffectedRowsCount();
        }
    }
}
