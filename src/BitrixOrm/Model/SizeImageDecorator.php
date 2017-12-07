<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\BitrixOrm\Model\Interfaces\SizeImageInterface;

/**
 * Class SizeFileDecorator
 *
 * @package FourPaws\BitrixOrm\Model
 */
class SizeImageDecorator extends Image implements SizeImageInterface
{
    /**
     * @return string
     */
    public function getSrc() : string
    {
        return sprintf('/size%s', parent::getSrc());
    }
}
