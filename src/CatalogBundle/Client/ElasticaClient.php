<?php

namespace FourPaws\CatalogBundle\Client;

use Elastica\Client;

/**
 * Class ElasticaClient
 *
 * @package FourPaws\CatalogBundle\Client
 */
class ElasticaClient implements ClientInterface
{
    /**
     * @var Client
     */
    private $adapter;
    /**
     * @var Client
     */
    private $client;

    /**
     * ElasticaClient constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setAdapter(string $index, string $type): void
    {

    }
}
