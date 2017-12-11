<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

/**
 * Interface ResizeableImageInterface
 *
 * @package FourPaws\BitrixOrm\Model\Interfaces
 */
interface ResizeImageInterface extends FileInterface
{
    /**
     * @param int $resizeWidth
     */
    public function setResizeWidth(int $resizeWidth);
    
    /**
     * @param int $resizeHeight
     */
    public function setResizeHeight(int $resizeHeight);
    
    /**
     * @return int
     */
    public function getResizeWidth() : int;
    
    /**
     * @return int
     */
    public function getResizeHeight() : int;
}
