<?php

namespace FourPaws\Migrator\Provider;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\ScalarField;
use FourPaws\Migrator\Converter\ConverterInterface;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Entity\EntityTable;
use FourPaws\Migrator\Entity\LazyTable;
use FourPaws\Migrator\Entity\MapTable;
use FourPaws\Migrator\Entity\UpdateResult;
use FourPaws\Migrator\Provider\Exceptions\FailResponseException;
use FourPaws\Migrator\StateTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class ProviderAbstract implements ProviderInterface, LoggerAwareInterface
{
    use StateTrait;
    use LoggerAwareTrait;
    
    /**
     * @var EntityInterface
     */
    protected $entity;
    
    protected $entityName;
    
    protected $external = [];
    
    protected $savedIds = [];
    
    /**
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }
    
    /**
     * @inheritdoc
     */
    abstract public function getMap() : array;
    
    /**
     * @return \FourPaws\Migrator\Converter\ConverterInterface[] array
     */
    public function getConverters() : array
    {
        return [];
    }
    
    /**
     * @param \FourPaws\Migrator\Entity\EntityInterface $entity
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }
    
    /**
     * ProviderAbstract constructor.
     *
     * @param \FourPaws\Migrator\Entity\EntityInterface $entity
     *
     * @internal param string $entityName
     *
     * @throws \RuntimeException
     */
    public function __construct(EntityInterface $entity)
    {
        $this->setEntity($entity);
        $this->entityName = $entity->getEntity();
        $this->setLogger(LoggerFactory::create('migrate_provider_' . $this->entityName));
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
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     */
    public function prepareData(array $data) : array
    {
        $result = [];
        
        $data = $this->setLazyEntities($data);
        
        foreach ($this->getMap() as $from => $to) {
            if ($data[$from]) {
                $result[$to] = $data[$from];
            }
        }
        
        foreach ($this->getConverters() as $converter) {
            if (!$converter instanceof ConverterInterface) {
                throw new \RuntimeException("Unknown converter: {$converter}");
            }
            
            $result = $converter->convert($result);
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponseException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function save(Response $response)
    {
        $lastTimestamp = 0;
        $entity        = $this->entity;
        
        $this->startTimer();
        $this->installEntity();
        $parsed = $this->parseResponse($response);
        
        if (!isset($parsed[$this->entityName])) {
            throw new FailResponseException('Entity name is not found in response.');
        }
        
        foreach ($parsed[$this->entityName] as $item) {
            $primary   = $entity->getPrimaryByItem($item);
            $timestamp = $entity->getTimestampByItem($item);
            $item      = $this->prepareData($item);
            
            try {
                $result = $entity->addOrUpdateItem($primary, $item);
                
                if (!$result->getResult()) {
                    /**
                     * @todo придумать сюда нормальный exception
                     */
                    throw new \Exception('Something happened with entity ' . $this->entityName . ' and primary '
                                         . $primary);
                } elseif ($this->external[$primary]) {
                    $this->external[$primary]['INTERNAL_ID'] = $result->getInternalId();
                }
                
                $this->savedIds[$primary] = $result->getInternalId();
                
                $lastTimestamp = strtotime($timestamp) > $lastTimestamp ? strtotime($timestamp) : $lastTimestamp;
                
                if ($result instanceof UpdateResult) {
                    $this->incUpdate();
                } else {
                    $this->incAdd();
                }
            } catch (\Exception $e) {
                EntityTable::pushBroken($this->entityName, $primary);
                $this->incError();
                $this->getLogger()->error($e->getMessage());
            }
        }
        
        $this->saveLazy();
        $this->handleLazy();
        
        $this->getLogger()->info(vsprintf('Migration %s cleared: time - %s, full count %d, add %d, update %d, error %d',
                                          [
                                              $this->entityName,
                                              $this->getFormattedTime(),
                                              $this->getFullCount(),
                                              $this->getAddCount(),
                                              $this->getUpdateCount(),
                                              $this->getErrorCount(),
                                          ]));
        
        if ($lastTimestamp) {
            EntityTable::updateEntity($this->entityName, $lastTimestamp);
        }
    }
    
    /**
     * Install default entity
     *
     * @throws \Exception
     */
    public function installEntity()
    {
        if (!$this->entityAlreadyExists()) {
            $this->entity->setDefaults();
            
            $result = EntityTable::addEntity($this->entityName);
            
            if (!$result->isSuccess()) {
                $this->getLogger()->error("Entity add error: \n" . implode("\n", $result->getErrors()));
            }
        }
    }
    
    /**
     * @return bool
     */
    public function entityAlreadyExists() : bool
    {
        return EntityTable::getByPrimary($this->entityName, ['select' => ['ENTITY']])->getSelectedRowsCount() === 1;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function setLazyEntities(array $data) : array
    {
        $primaryKey = $this->entity->getPrimary();
        
        foreach ($this->getMap() as $from => $to) {
            if (strpos($from, '.')) {
                $ef = explode('.', $from);
                
                if (!$data[$ef[1]]) {
                    continue;
                }
                
                /**
                 * @todo оптимизировать - криво, на одну запись - один запрос в БД
                 */
                $exists = MapTable::getInternalIdByExternalId($data[$ef[1]], $ef[0]);
                
                if ($exists) {
                    $data[$ef[1]] = $exists;
                } else {
                    $this->external[$data[$primaryKey]]['ENTITIES'][] = [
                        'EXTERNAL_ID' => $data[$ef[1]],
                        'FIELD'       => $ef[1],
                        'ENTITY_FROM' => $this->entityName,
                        'ENTITY_TO'   => $ef[0],
                    ];
                    
                    unset($data[$from]);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Save lazy fields from $this->external
     *
     * @todo добавить LazyException
     *
     * @throws \Exception
     */
    public function saveLazy()
    {
        foreach ($this->external as $externalList) {
            foreach ($externalList['ENTITIES'] as $entity) {
                if ($externalList['INTERNAL_ID']) {
                    LazyTable::add(array_merge(['INTERNAL_ID' => $externalList['INTERNAL_ID']], $entity));
                }
            }
        }
    }
    
    /**
     * Обрабатываем сохранённые сущности - вдруг у нас что-то ссылается на них
     *
     * @todo добавить LazyException
     *
     * @throws \Exception
     */
    public function handleLazy()
    {
        if (!$this->savedIds) {
            return;
        }
        
        LazyTable::handleLazy($this->entityName, $this->savedIds);
    }
}
