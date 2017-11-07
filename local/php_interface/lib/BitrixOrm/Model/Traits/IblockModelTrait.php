<?php

namespace FourPaws\BitrixOrm\Model\Traits;

trait IblockModelTrait
{
    
    /**
     * @var int
     */
    protected $IBLOCK_ID = 0;
    
    /**
     * @var int
     */
    protected $SORT = 500;
    
    /**
     * @var string
     */
    protected $NAME = '';
    
    /**
     * @var string
     */
    protected $CODE = '';
    
    /**
     * @var string
     */
    protected $LIST_PAGE_URL = '';
    
    /**
     * @return int
     */
    public function getIblockId() : int
    {
        return (int)$this->IBLOCK_ID;
    }
    
    /**
     * @param int $iblockId
     *
     * @return $this
     */
    public function withIblockId(int $iblockId)
    {
        $this->IBLOCK_ID = $iblockId;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getListPageUrl() : string
    {
        return $this->LIST_PAGE_URL;
    }
    
    /**
     * @param string $url
     *
     * @return $this
     */
    public function withListPageUrl(string $url)
    {
        $this->LIST_PAGE_URL = $url;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->NAME;
    }
    
    /**
     * @param string $NAME
     *
     * @return $this
     */
    public function withName(string $NAME)
    {
        $this->NAME = $NAME;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getSort() : int
    {
        return (int)$this->SORT;
    }
    
    /**
     * @param int $sort
     *
     * @return $this
     */
    public function withSort(int $sort)
    {
        $this->SORT = $sort;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCode() : string
    {
        return $this->CODE;
    }
    
    /**
     * @param string $CODE
     *
     * @return $this
     */
    public function withCode(string $CODE)
    {
        $this->CODE = $CODE;
        
        return $this;
    }
}
