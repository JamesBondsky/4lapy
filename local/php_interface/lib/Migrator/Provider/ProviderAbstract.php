<?php

namespace FourPaws\Migrator\Provider;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\ScalarField;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Provider\Exceptions\FailResponseException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use FourPaws\Migrator\Entity\EntityTable;

abstract class ProviderAbstract implements ProviderInterface, LoggerAwareInterface
{
    /**
     * @var EntityInterface
     */
    protected $entity;
    
    protected $entityName;
    
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
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * @return array
     */
    abstract public function getMap() : array;
    
    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }
    
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }
    
    /**
     * ProviderAbstract constructor.
     *
     * @param string                                    $entityName
     * @param \FourPaws\Migrator\Entity\EntityInterface $entity
     */
    public function __construct(string $entityName, EntityInterface $entity)
    {
        $this->setEntityName($entityName);
        $this->setEntity($entity);
        $this->setLogger(LoggerFactory::create('migrate_provider_' . $entityName));
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return mixed
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponseException
     */
    protected function parseResponse(Response $response)
    {
        if (!$response->isOk()) {
            throw new FailResponseException($response->getContent(), $response->getStatusCode());
        }
        
        /**
         * @todo переделать на специально обученные классы
         */
        return json_decode($response->getContent(),
                           JSON_FORCE_OBJECT | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_APOS);
    }
    
    /**
     * @todo убрать прочь в какие-нибудь utils для ORM
     *
     * @return \Closure to use in array_filter()
     */
    public function getScalarEntityMapFilter() : \Closure
    {
        return function ($value) {
            $whenArray  = is_array($value) && !$value['expression'] && !$value['reference'];
            $whenObject = $value instanceof ScalarField;
            
            return $whenArray || $whenObject;
        };
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareData(array $data)
    {
        $result = [];
        
        foreach ($this->getMap() as $from => $to) {
            $result[$to] = $data[$from];
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        $lastTimestamp = 0;
        $entity        = $this->entity;
        
        $this->installEntity();
        
        foreach (($this->parseResponse($response))[$this->entityName] as $item) {
            $primary   = $entity->getPrimaryByItem($item);
            $timestamp = $entity->getTimestampByItem($item);
            $item      = $this->prepareData($item);
            
            try {
                $result = $entity->addOrUpdateItem($primary, $item);
                
                if (!$result->getResult()) {
                    /**
                     * @todo придумать сюда нормальный exception
                     */
                    throw new \Exception('Something happened with entity' . $this->entityName . ' and primary '
                                         . $primary);
                }
                
                $lastTimestamp = strtotime($timestamp) > $lastTimestamp ? strtotime($timestamp) : $lastTimestamp;
            } catch (\Throwable $e) {
                EntityTable::pushBroken($this->entity, $primary);
                $this->getLogger()->error($e->getMessage(), $e->getTrace());
            }
        }
        
        if ($lastTimestamp) {
            EntityTable::updateEntity($this->entity, $lastTimestamp);
        }
    }
    
    /**
     * Install default entity
     */
    public function installEntity()
    {
        if (!$this->entityAlreadyExists()) {
            $result = EntityTable::addEntity($this->entityName);
            
            if (!$result->isSuccess()) {
                $this->getLogger()->error("Entity add error: \n" . implode("\n", $result->getErrors()));
            }
            
            $this->entity->setDefaults();
        }
    }
    
    /**
     * @return bool
     */
    public function entityAlreadyExists() : bool
    {
        return EntityTable::getByPrimary($this->entityName, ['select' => ['ENTITY']])->getSelectedRowsCount() === 1;
    }
}