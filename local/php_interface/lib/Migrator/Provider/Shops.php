<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Entity\Result;
use Symfony\Component\HttpFoundation\Response;

class Shops extends IBlockProvider
{
    
    /**
     * @return array
     */
    public function getMap() : array
    {
        // TODO: Implement getMap() method.
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        // TODO: Implement save() method.
    }
    
    /**
     * @todo унести в модель
     *
     * @param array $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(array $data) : Result
    {
        // TODO: Implement addItem() method.
    }
    
    /**
     * @todo унести в модель
     *
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
        // TODO: Implement updateItem() method.
    }
}