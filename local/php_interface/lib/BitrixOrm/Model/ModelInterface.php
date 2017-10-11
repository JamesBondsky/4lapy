<?php

namespace FourPaws\BitrixOrm\Model;

interface ModelInterface
{
    /**
     * ModelInterface constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = []);
    
    /**
     * @param int $primary
     *
     * @return \FourPaws\BitrixOrm\Model\ModelInterface
     */
    public static function createFromPrimary(int $primary) : self;
}