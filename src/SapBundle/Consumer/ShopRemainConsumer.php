<?php

namespace FourPaws\SapBundle\Consumer;

class ShopRemainConsumer implements ConsumerInterface
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
    }
}
