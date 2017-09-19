<?php

namespace FourPaws\Migrator\Client;

interface Saveable
{
    /**
     * @return bool
     */
    public function save() : bool;
}