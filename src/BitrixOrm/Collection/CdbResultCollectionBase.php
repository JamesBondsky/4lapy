<?php

namespace FourPaws\BitrixOrm\Collection;

abstract class CdbResultCollectionBase extends CollectionBase
{
    protected $cdbResult;
    
    /**
     * @return \CDBResult
     */
    public function getCdbResult() : \CDBResult
    {
        return $this->cdbResult;
    }
    
    /**
     * @return int
     */
    public function getTotalCount() : int
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
                $this->set($value['ID'] ?: $key, $value);
            }
            
            $this->totalCount = $this->count();
        } else {
            parent::populateCollection();
            
            $this->totalCount = (int)$this->cdbResult->AffectedRowsCount();
        }
    }
}