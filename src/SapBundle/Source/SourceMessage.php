<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Model\SourceMessageInterface;

class SourceMessage implements SourceMessageInterface
{
    private $id;
    
    private $type;
    
    private $data;
    
    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * @return string
     */
    public function getData() : string
    {
        return $this->data;
    }
    
    /**
     * Create source message
     *
     * @param        $id
     * @param string $type
     * @param string $data
     */
    public function __construct(string $id, string $type, string $data)
    {
        $this->id   = $id;
        $this->type = $type;
        $this->data = $data;
    }
}
