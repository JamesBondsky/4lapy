<?php

namespace FourPaws\Migrator\Entity;

class Result
{
    private $result;
    
    private $internalId;
    
    /**
     * @return bool
     */
    public function getResult() : bool
    {
        return $this->result;
    }
    
    /**
     * @param bool $result
     */
    private function setResult(bool $result)
    {
        $this->result = $result;
    }
    
    /**
     * @return string
     */
    public function getInternalId() : string
    {
        return $this->internalId;
    }
    
    /**
     * @param string $internalId
     */
    private function setInternalId(string $internalId)
    {
        $this->internalId = $internalId;
    }
    
    /**
     * Result constructor.
     *
     * @param bool   $result
     * @param string $internalId
     */
    public function __construct($result, $internalId = null)
    {
        $this->setResult($result);
        $this->setInternalId($internalId);
    }
}