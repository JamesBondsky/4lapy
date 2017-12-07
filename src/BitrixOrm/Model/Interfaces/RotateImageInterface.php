<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

/**
 * Interface RotateImageInterface
 *
 * @package FourPaws\BitrixOrm\Model\Interfaces
 */
interface RotateImageInterface extends FileInterface
{
    /**
     * @param int $angle
     */
    public function setAngle(int $angle);
    
    /**
     * @return int
     */
    public function getAngle() : int;
}
