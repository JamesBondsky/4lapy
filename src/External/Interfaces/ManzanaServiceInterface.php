<?php

namespace FourPaws\External\Interfaces;

use JMS\Serializer\SerializerInterface;
use Meng\AsyncSoap\SoapClientInterface;

interface ManzanaServiceInterface
{
    public function __construct(SerializerInterface $serializer, SoapClientInterface $client, array $parameters);
}
