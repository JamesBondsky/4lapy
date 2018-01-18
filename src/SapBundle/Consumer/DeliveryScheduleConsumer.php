<?php

namespace FourPaws\SapBundle\Consumer;

class DeliveryScheduleConsumer implements ConsumerInterface
{
    public function consume($scheduleInfo) : bool
    {
        dump($scheduleInfo);
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
