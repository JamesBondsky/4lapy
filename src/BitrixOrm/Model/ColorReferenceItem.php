<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\BitrixOrm\Model;


class ColorReferenceItem extends HlbReferenceItem
{
    /**
     * @var string
     */
    protected $UF_COLOUR_CODE = '';

    /**
     * @return string
     */
    public function getColorCode(): string
    {
        return $this->UF_COLOUR_CODE;
    }

    /**
     * @param $colorCode
     *
     * @return $this
     */
    public function withColorCode($colorCode)
    {
        $this->UF_COLOUR_CODE = $colorCode;

        return $this;
    }
}
