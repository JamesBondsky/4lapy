<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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
