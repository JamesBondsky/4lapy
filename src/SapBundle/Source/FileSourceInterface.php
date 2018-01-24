<?php

namespace FourPaws\SapBundle\Source;

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
