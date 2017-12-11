<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

interface ModelInterface
{
    /**
     * ModelInterface constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = []);
    
    /**
     * @param string $primary
     */
    public static function createFromPrimary(string $primary);
}
