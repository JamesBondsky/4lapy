<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

class StockRemainConsumer implements ConsumerInterface
{
    public function consume($remainInfo) : bool
    {
        dump($remainInfo);
        die();
    }
    
    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data) : bool
    {
        /**
         * @todo implement
         */
        return false;
    }
}
