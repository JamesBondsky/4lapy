<?php

namespace FourPaws\Migrator\Provider;

use Symfony\Component\HttpFoundation\Response;

interface ProviderInterface
{
    /**
     * @return array
     */
    public function getMap() : array;
    
    /**
     * Get primary table name
     *
     * @return string
     */
    public function getPrimary() : string;
    
    /**
     * Get timestamp field name
     *
     * @return string
     */
    public function getTimestamp() : string;
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response);
    
    /**
     * @param string $entityName
     *
     * @return void
     */
    public function setEntityName(string $entityName);
}