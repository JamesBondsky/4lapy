<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Provider\ProviderInterface;

interface ClientInterface extends Saveable
{
    const ENTITY_NAME = '';
    
    public function query();

    public function getProvider() : ProviderInterface;
    
    /**
     * ClientInterface constructor.
     *
     * @param \FourPaws\Migrator\Provider\ProviderInterface $provider
     * @param array                                         $options
     */
    public function __construct(ProviderInterface $provider, array $options = []);
}