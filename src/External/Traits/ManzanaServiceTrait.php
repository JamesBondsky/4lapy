<?php

namespace FourPaws\External\Traits;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Exceptions\ApplicationCreateException;
use JMS\Serializer\SerializerInterface;
use Meng\AsyncSoap\SoapClientInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

trait ManzanaServiceTrait
{
    use LoggerAwareTrait;
    
    protected $client;
    
    protected $serializer;
    
    protected $parameters;
    
    /**
     * ManzanaService constructor.
     *
     * @param SerializerInterface $serializer
     * @param SoapClientInterface $client
     * @param array               $parameters
     *
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct(SerializerInterface $serializer, SoapClientInterface $client, array $parameters)
    {
        $this->serializer = $serializer;
        $this->client     = $client;
        $this->parameters = $parameters;
    }
    
    /**
     * @throws \RuntimeException
     */
    public function setServiceLogger()
    {
        if (!$this->logger) {
            $this->setLogger(LoggerFactory::create('manzana'));
        }
    }
    
}
