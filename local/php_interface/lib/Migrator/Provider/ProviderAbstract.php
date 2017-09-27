<?php

namespace FourPaws\Migrator\Provider;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\ScalarField;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Entity\LazyTable;
use FourPaws\Migrator\Entity\MapTable;
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
    
    protected $external;
    
    protected $savedIds = [];
    
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
     * $map - однозначное отображение ['поле на сервере' => 'поле на клиенте']
     * Так же возможно однозначное указание сущности для позднего связывания.
     *
     * Работает следующим образом:
     *
     * Отображение задаётся в виде ['имя сущности'.'поле на сервере' => 'поле на клиенте']
     *
     * При разборе ответа вместо записи в это поле осуществляется запись в таблицу adv_migrator_lazy
     * При любом импорте провайдер после завершения импорта разбирает относящиеся к своей сущности id'шники и, если
     * у него есть, что отдать, записывает значение, удаляя его из таблицы.
     *
     * @return array
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
        
        $data = $this->setLazyEntities($data);
        
        foreach ($this->getMap() as $from => $to) {
            if ($data[$from]) {
                $result[$to] = $data[$from];
            }
        }
        
        foreach ($this->getConverters() as $converter) {
            $result = $converter->convert($result);
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponseException
     */
    public function save(Response $response)
    {
        $lastTimestamp = 0;
        $entity        = $this->entity;
        
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
            } catch (\Throwable $e) {
                EntityTable::pushBroken($this->entityName, $primary);
                $this->getLogger()->error($e->getMessage());
            }
        }
        
        $this->saveLazy();
        $this->handleLazy();
        
        if ($lastTimestamp) {
            EntityTable::updateEntity($this->entityName, $lastTimestamp);
        }
    }
    
    /**
     * Install default entity
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
     */
    public function handleLazy()
    {
        if (!$this->savedIds) {
            return;
        }
        
        $lazyCollection = LazyTable::getLazyByIdList($this->entityName, $this->savedIds);
        /**
         * @todo implement this
         */
    }
}