<?php

namespace FourPaws\Migrator\Entity;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractEntity implements EntityInterface, LoggerAwareInterface
{
    protected $entity;
    
    protected $provider;
    
    protected $logger;
    
    abstract public function setDefaults();
    
    public function checkEntity()
    {
        return EntityTable::getList([
                                        'select' => ['ENTITY'],
                                        'filter' => ['ENTITY' => $this->entity],
                                        'limit'  => 1,
                                    ])->getSelectedRowsCount() === 1;
    }
    
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
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * By default - ID
     *
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }
    
    /**
     * By default - TIMESTAMP_X
     *
     * @return string
     */
    public function getTimestamp() : string
    {
        return 'TIMESTAMP_X';
    }
    
    /**
     * AbstractEntity constructor.
     *
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entity   = $entity;
        $this->setLogger(LoggerFactory::create('migrate_provider_' . $entity));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    abstract public function addItem(string $primary, array $data) : Result;
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    abstract public function updateItem(string $primary, array $data) : Result;
    
    /**
     * @param string $primary
     * @param array  $item
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addOrUpdateItem(string $primary, array $item) : Result
    {
        if (MapTable::isInternalEntityExists($primary, $this->entity)) {
            $this->getLogger()->info("Update {$this->entity} with id {$primary}...\n");
            $result = $this->updateItem($primary, $item);
        } else {
            $this->getLogger()->info("Create {$this->entity} with id {$primary}...\n");
            $result = $this->addItem($primary, $item);
        }
        
        $result->getResult() ? $this->getLogger()->info("success\n\n") : $this->getLogger()->error("error\n\n");
        
        return $result;
    }
    
    /**
     * @param array $item
     *
     * @return string
     */
    public function getPrimaryByItem(array $item) : string
    {
        return $item[$this->getPrimary()];
    }
    
    /**
     * @param array $item
     *
     * @return string
     */
    public function getTimestampByItem(array $item) : string
    {
        return $item[$this->getTimestamp()];
    }
}