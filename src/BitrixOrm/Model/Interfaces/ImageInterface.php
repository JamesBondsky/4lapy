<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

/**
 * Interface ImageInterface
 *
 * @package FourPaws\BitrixOrm\Model
 */
interface ImageInterface extends FileInterface
{
    /**
     * @param int $height
     *
     * @return static
     */
    public function setHeight($height);
    
    /**
     * @return int
     */
    public function getHeight() : int;
    
    /**
     * @param int $width
     *
     * @return static
     */
    public function setWidth($width);
    
    /**
     * @return int
     */
    public function getWidth() : int;
}
