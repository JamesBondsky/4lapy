<?php

namespace FourPaws\Migrator\Provider;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Migrator\Converter\ConverterInterface;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Entity\EntityTable;
use FourPaws\Migrator\Entity\MapTable;
use FourPaws\Migrator\StateTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class MockProviderAbstract implements ProviderInterface, LoggerAwareInterface
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
    public function getMap() : array
    {
        return [];
    }
    
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
     *
     * @throws \RuntimeException
     */
    public function __construct(string $entityName, EntityInterface $entity)
    {
        $this->setEntityName($entityName);
        $this->setEntity($entity);
        $this->setLogger(LoggerFactory::create('migrate_provider_' . $entityName));
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
                throw new \RuntimeException(sprintf('Unknown converter: %s', $converter));
            }
            
            $result = $converter->convert($result);
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponseException
     * @throws \Exception
     */
    public function save(Response $response)
    {
        $this->startTimer();
        $this->installEntity();
        $this->getLogger()->info(vsprintf('Migration %s cleared: mapping.',
                                          [
                                              $this->entityName,
                                          ]));
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
}
