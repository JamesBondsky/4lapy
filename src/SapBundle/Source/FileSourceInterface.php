<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Model\SourceMessageInterface;

interface FileSourceInterface extends SourceMessageInterface
{
    /**
     * Get filename
     *
     * @return string
     */
    public function getName() : string;
    
    /**
     * Get path
     *
     * @return string
     */
    public function getDirectory() : string;
}
