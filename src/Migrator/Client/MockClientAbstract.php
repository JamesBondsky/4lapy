<?php

namespace FourPaws\Migrator\Client;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Migrator\Provider\ProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockClientAbstract
 *
 * Мок. Если нам не нужно делать запросы а нужно, например, установить дефолтный маппинг
 *
 * @package FourPaws\Migrator\Client
 */
abstract class MockClientAbstract implements ClientInterface, LoggerAwareInterface
{
    const ENTITY_NAME = '';
    
    protected $provider;
    
    protected $logger;
    
    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }
    
    /**
     * ClientAbstract constructor.
     *
     * @param \FourPaws\Migrator\Provider\ProviderInterface $provider
     * @param array                                         $options
     *
     * @throws \RuntimeException
     */
    public function __construct(ProviderInterface $provider, array $options = [])
    {
        $this->provider = $provider;
        
        $this->setLogger(LoggerFactory::create('migrator_' . static::ENTITY_NAME));
    }
    
    /**
     * @return \FourPaws\Migrator\Provider\ProviderInterface
     */
    public function getProvider() : ProviderInterface
    {
        return $this->provider;
    }
    
    /**
     * @return bool
     */
    public function save() : bool
    {
        try {
            $this->getProvider()->save($this->query());
            
            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            
            return false;
        }
    }
    
    /**
     * @return Response
     *
     * @throws \InvalidArgumentException
     */
    public function query() : Response
    {
        return new Response();
    }
    
    /**
     * @return int
     */
    public function getLastTimestamp() : int
    {
        return 0;
    }
}
