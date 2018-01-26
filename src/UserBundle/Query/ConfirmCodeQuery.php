<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\UserBundle\Collection\ConfirmCodeCollection;

/**
 * Class ConfirmCodeQuery
 *
 * @package FourPaws\UserBundle\Query
 */
class ConfirmCodeQuery extends D7QueryBase
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect() : array
    {
        return ['*'];
    }
    
    /**
     * @inheritdoc
     */
    public function getBaseFilter() : array
    {
        return [];
    }
    
    /**
     * @inheritdoc
     */
    public function exec() : CollectionBase
    {
        return new ConfirmCodeCollection($this->doExec());
    }
}
