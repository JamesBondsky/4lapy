<?php

namespace FourPaws\External\Interfaces;

use JMS\Serializer\Serializer;
use Meng\AsyncSoap\SoapClientInterface;

interface ManzanaServiceInterface
{
    public function __construct(Serializer $serializer, SoapClientInterface $client, array $parameters);
}
