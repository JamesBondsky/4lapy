<?php

namespace FourPaws\SapBundle\Consumer;

class OrderStatusConsumer implements ConsumerInterface
{
    public function consume($orderInfo) : bool
    {
        dump($orderInfo);
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
