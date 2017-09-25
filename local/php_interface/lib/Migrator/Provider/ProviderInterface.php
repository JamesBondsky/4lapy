<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Response;

interface ProviderInterface
{
    /**
     * @return array
     */
    public function getMap() : array;
    
    /**
     * @return \FourPaws\Migrator\Converter\ConverterInterface[] array
     */
    public function getConverters() : array;

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response);
    
    /**
     * @param EntityInterface $entity
     *
     * @return void
     */
    public function setEntity(EntityInterface $entity);
}