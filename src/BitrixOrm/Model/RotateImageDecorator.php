<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\BitrixOrm\Model\Exceptions\WrongRotateAngleException;
use FourPaws\BitrixOrm\Model\Interfaces\RotateImageInterface;

/**
 * Class RotateImageDecorator
 *
 * @package FourPaws\BitrixOrm\Model
 */
class RotateImageDecorator extends Image implements RotateImageInterface
{
    const ALLOWABLE_ANGLE = [
        90,
        180,
        270,
    ];
    
    /**
     * @var int
     */
    protected $angle;
    
    /**
     * @param int $angle
     *
     * @throws WrongRotateAngleException
     * @return $this
     *
     */
    public function setAngle(int $angle)
    {
        if (!\in_array($angle, self::ALLOWABLE_ANGLE, true)) {
            throw new WrongRotateAngleException(sprintf(
                'Only %s angle value allowed',
                                                        implode(', ', self::ALLOWABLE_ANGLE)
            ));
        }
        
        $this->angle = $angle;
    
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getAngle() : int
    {
        return $this->angle;
    }
    
    /**
     * @return string
     */
    public function getSrc() : string
    {
        $angle = $this->getAngle();
        
        if ($angle) {
            return sprintf('/rotate/%s%s', $this->getAngle(), parent::getSrc());
        }
        
        return parent::getSrc();
    }
}
