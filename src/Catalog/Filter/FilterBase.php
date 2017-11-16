<?php

namespace FourPaws\Catalog\Filter;

use FourPaws\BitrixOrm\Model\HlbItemBase;

abstract class FilterBase extends HLBItemBase implements FilterInterface
{
    use FilterTrait;

    /**
     * @var string
     */
    protected $PROP_CODE = '';

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
    }

    /**
     * @return string
     */
    public function getPropCode(): string
    {
        return $this->PROP_CODE;
    }

    /**
     * @param string $propCode
     *
     * @return $this
     */
    public function withPropCode(string $propCode)
    {
        $this->PROP_CODE = $propCode;

        return $this;
    }

}
