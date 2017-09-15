<?php

namespace FourPaws\Migrator\Entity;

class Result
{
    private $result;
    
    private $timestamp;
    
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
    public function getTimestamp() : string
    {
        return $this->timestamp;
    }
    
    /**
     * @param string $timestamp
     */
    private function setTimestamp(string $timestamp)
    {
        $this->timestamp = $timestamp;
    }
    
    /**
     * Result constructor.
     *
     * @param bool $result
     * @param int  $timestamp
     */
    public function __construct($result, $timestamp = null)
    {
        $this->setResult($result);
        $this->setTimestamp($timestamp);
    }
}