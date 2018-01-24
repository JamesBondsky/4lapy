<?php

namespace FourPaws\SapBundle\Source;

interface SourceMessageInterface
{
    /**
     * @return string
     */
    public function getId() : string;
    
    /**
     * @return string
     */
    public function getType() : string;
    
    /**
     * @return mixed
     */
    public function getData();
}
