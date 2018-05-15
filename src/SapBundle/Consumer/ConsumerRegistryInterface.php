<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

interface ConsumerRegistryInterface
{
    /**
     * @param ConsumerInterface $consumer
     *
     * @return ConsumerRegistryInterface
     */
    public function register(ConsumerInterface $consumer): ConsumerRegistryInterface;

    /**
     * @param $data
     *
     * @return bool
     */
    public function consume($data): bool;
}
