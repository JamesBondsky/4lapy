<?php

namespace FourPaws\SapBundle\Consumer;

interface ConsumerInterface
{
    /**
     * @param $data
     *
     * @return bool
     */
    public function consume($data): bool;

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool;
}
