<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Entity\Result;
use Symfony\Component\HttpFoundation\Response;

class News extends IBlockProvider
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        $map = parent::getMap();

        $map = array_merge($map, [
            'PROPERTY_'
        ]);

        return $map;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        // TODO: Implement save() method.
    }
}