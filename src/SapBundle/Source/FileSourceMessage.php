<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Source;

/**
 * Class FileSourceMessage
 *
 * @package FourPaws\SapBundle\Source
 */
class FileSourceMessage extends SourceMessage implements FileSourceInterface
{
    private $directory;
    
    private $name;
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }
    
    /**
     * @param string $directory
     *
     * @return $this
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;
        
        return $this;
    }
}
