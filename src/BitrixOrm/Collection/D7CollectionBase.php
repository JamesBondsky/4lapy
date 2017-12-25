<?php

namespace FourPaws\BitrixOrm\Collection;

use Bitrix\Main\DB\Result;

abstract class D7CollectionBase extends CollectionBase
{
    /**
     * @var Result
     */
    protected $result;

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    /**
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
