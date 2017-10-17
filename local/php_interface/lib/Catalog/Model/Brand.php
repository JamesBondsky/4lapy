<?php

namespace FourPaws\Catalog\Model;

use FourPaws\BitrixOrm\Model\IblockElement;

class Brand extends IblockElement
{

    /**
     * @var int
     */
    protected $PROPERTY_POPULAR = 0;

    /**
     * @return bool
     */
    public function isPopular(): bool
    {
        return (bool)$this->PROPERTY_POPULAR;
    }

    /**
     * @param bool $popular
     *
     * @return $this
     */
    public function withPopular(bool $popular)
    {
        $this->PROPERTY_POPULAR = (int)$popular;

        return $this;
    }


}
