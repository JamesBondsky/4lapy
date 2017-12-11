<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

/**
 * Interface CropableImageInterface
 *
 * @package FourPaws\BitrixOrm\Model\Interfaces
 */
interface CropImageInterface extends FileInterface
{
    /**
     * @param int $cropWidth
     */
    public function setCropWidth(int $cropWidth);
    
    /**
     * @param int $cropHeight
     */
    public function setCropHeight(int $cropHeight);
    
    /**
     * @return int
     */
    public function getCropWidth() : int;
    
    /**
     * @return int
     */
    public function getCropHeight() : int;
}
