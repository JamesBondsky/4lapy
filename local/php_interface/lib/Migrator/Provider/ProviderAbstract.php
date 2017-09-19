<?php

namespace FourPaws\Migrator\Provider;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\ScalarField;
use FourPaws\Migrator\Entity\MapTable;
use FourPaws\Migrator\Entity\Result;
use FourPaws\Migrator\Provider\Exceptions\FailResponse;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use FourPaws\Migrator\Entity\EntityTable;

abstract class ProviderAbstract implements ProviderInterface, LoggerAwareInterface
{
    protected $entity;
    
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
     * @return string
     */
    abstract public function getPrimary() : string;
    
    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entity = $entityName;
    }
    
    /**
     * ProviderAbstract constructor.
     *
     * @param string $entityName
     */
    public function __construct(string $entityName)
    {
        $this->setEntityName($entityName);
        $this->setLogger(LoggerFactory::create('migrate_provider_' . $entityName));
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return mixed
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponse
     */
    protected function parseResponse(Response $response)
    {
        if (!$response->isOk()) {
            throw new FailResponse($response->getContent(), $response->getStatusCode());
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
     * @todo непонятно, нахера тут. Отрефакторить?
     *
     * @param bool $result
     * @param int  $timestamp
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function getItemResultObject(bool $result, int $timestamp = null) : Result
    {
        return new Result($result, $timestamp);
    }
    
    /**
     * @todo single responsibility?! Вынести в Entity.
     *
     * @param array $item
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addOrUpdateItem(array $item) : Result
    {
        $primary = $item[$this->getPrimary()];
        unset($item[$primary]);
        
        if (MapTable::isInternalEntityExists($item[$this->getPrimary()], $this->entity)) {
            return $this->updateItem($primary, $item);
        } else {
            return $this->addItem($item);
        }
    }
    
    /**
     * @todo single responsibility?! Вынести в Entity.
     *
     * @param array $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    abstract function addItem(array $data) : Result;
    
    /**
     * @todo single responsibility?! Вынести в Entity.
     *
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    abstract function updateItem(string $primary, array $data) : Result;

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        $lastTimestamp = 0;
        
        foreach ($this->parseResponse($response) as $item) {
            
            try {
                $result = $this->addOrUpdateItem($item);
                
                if (!$result->getResult()) {
                    /**
                     * @todo придумать сюда нормальный exception
                     */
                    throw new \Exception('Something happened with entity' . $this->entity . ' and primary '
                                         . $item[$this->getPrimary()]);
                }

                $lastTimestamp =
                    strtotime($item[$this->getTimestamp()])
                    > $lastTimestamp ? strtotime($item[$this->getTimestamp()]) : $lastTimestamp;
            } catch (\Throwable $e) {
                EntityTable::pushBroken($this->entity, $item[$this->getPrimary()]);
                $this->getLogger()->error($e->getMessage(), $e->getTrace());
            }
        }
        
        if ($lastTimestamp) {
            EntityTable::update($this->entity, ['TIMESTAMP' => $lastTimestamp]);
        }
    }
}