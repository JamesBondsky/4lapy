<?php

namespace FourPaws\AppBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\UI\PageNavigation;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Serialization\ArrayOrFalseHandler;
use FourPaws\AppBundle\Serialization\BitrixBooleanHandler;
use FourPaws\AppBundle\Serialization\BitrixDateHandler;
use FourPaws\AppBundle\Serialization\BitrixDateTimeHandler;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use JMS\Serializer\Annotation\SkipWhenEmpty;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class BaseRepository
 *
 * @package FourPaws\AppBundle\Repository
 */
class BaseRepository
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    
    /** @var BaseEntity $entity */
    protected $entity;
    
    /** @var PageNavigation|null */
    protected $nav;
    
    /** @var Serializer $builder */
    protected $serializer;
    
    /**
     * @var DataManager
     */
    private $dataManager;
    
    /**
     * AddressRepository constructor.
     *
     * @param ValidatorInterface $validator
     *
     * @throws RuntimeException
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator  = $validator;
        $this->serializer = SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new BitrixDateHandler());
                $registry->registerSubscribingHandler(new BitrixDateHandler());
                $registry->registerSubscribingHandler(new BitrixDateTimeHandler());
                $registry->registerSubscribingHandler(new BitrixBooleanHandler());
                $registry->registerSubscribingHandler(new ArrayOrFalseHandler());
            }
        )->build();
    }
    
    /**
     * @return bool
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function create() : bool
    {
        if (!($this->entity instanceof BaseEntity)) {
            throw new BitrixRuntimeException('empty entity');
        }
        $validationResult = $this->validator->validate($this->entity, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }
        
        $res = $this->dataManager::add(
            $this->builder->toArray($this->entity, SerializationContext::create()->setGroups(['create']))
        );
        if ($res->isSuccess()) {
            $this->entity->setId($res->getId());
            
            return true;
        }
        
        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()));
    }
    
    /**
     * @return bool
     * @throws InvalidIdentifierException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     */
    public function update() : bool
    {
        if (!($this->entity instanceof BaseEntity)) {
            throw new BitrixRuntimeException('empty entity');
        }
        $this->checkIdentifier($this->entity->getId());
        $validationResult = $this->validator->validate(
            $this->entity,
            [
                new SkipWhenEmpty(),
            ]
            ['update']
        );
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }
        
        $res = $this->dataManager::update(
            $this->entity->getId(),
            $this->serializer->toArray($this->entity, SerializationContext::create()->setGroups(['update']))
        );
        if ($res->isSuccess()) {
            return true;
        }
        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()));
    }
    
    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    protected function checkIdentifier(int $id)
    {
        try {
            $result = $this->validator->validate(
                $id,
                [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1]),
                    new Type(['type' => 'integer']),
                ],
                [
                    'delete',
                    'update',
                ]
            );
        } catch (ValidatorException $exception) {
            throw new ConstraintDefinitionException('Wrong constraint configuration');
        }
        if ($result->count()) {
            throw new InvalidIdentifierException(sprintf('Wrong identifier %s passed', $id));
        }
    }
    
    /**
     * @param int $id
     *
     * @return bool
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     */
    public function delete(int $id) : bool
    {
        $this->checkIdentifier($id);
        $res = $this->dataManager::delete($id);
        if ($res->isSuccess()) {
            return true;
        }
        
        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()), $id ?: null);
    }
    
    /**
     * @param array $params
     *
     * @return BaseEntity[]|array
     * @throws \Exception
     */
    public function findBy(array $params = []) : array
    {
        if (!isset($params['select'])) {
            $params['select'] = ['*'];
        }
        $query = $this->dataManager::query()->setSelect($params['select']);
        if (!empty($params['filter'])) {
            $query->setFilter($params['filter']);
        }
        if (!empty($params['order'])) {
            $query->setOrder($params['order']);
        }
        if (!empty($params['limit'])) {
            $query->setLimit($params['limit']);
        }
        if (!empty($params['offset'])) {
            $query->setOffset($params['offset']);
        }
        if (!empty($params['ttl'])) {
            $query->setCacheTtl($params['ttl']);
        }
        if (!empty($params['group'])) {
            $query->setGroup($params['group']);
        }
        if (!empty($params['runtime'])) {
            if (\is_array($params['runtime'])) {
                foreach ($params['runtime'] as $runtime) {
                    $query->registerRuntimeField($runtime);
                }
            } else {
                $query->registerRuntimeField($params['runtime']);
            }
        }
        if ($this->nav instanceof PageNavigation) {
            $query->setOffset($this->nav->getOffset());
            $query->setLimit($this->nav->getLimit());
        }
        $result = $query->exec();
        if (0 === $result->getSelectedRowsCount()) {
            return [];
        }
        
        if ($this->nav instanceof PageNavigation) {
            $this->nav->setRecordCount($result->getCount());
        }
        
        $allItems = $result->fetchAll();
        if (!empty($params['entityClass'])) {
            return $this->serializer->fromArray(
                $allItems,
                sprintf('array<%s>', $params['entityClass']),
                DeserializationContext::create()->setGroups(['read'])
            );
        }
        
        return $allItems;
    }
    
    /**
     * @param array $filter
     *
     * @return int
     * @throws ObjectPropertyException
     */
    public function getCount(array $filter = []) : int
    {
        $query = $this->dataManager::query()->setCacheTtl(360000);
        $query->countTotal(true);
        if (!empty($filter)) {
            $query->setFilter($filter);
        }
        
        return $query->exec()->getCount();
    }
    
    /**
     * @param DataManager $dataManager
     *
     * @return BaseRepository
     */
    public function setDataManager(DataManager $dataManager) : BaseRepository
    {
        $this->dataManager = $dataManager;
        
        return $this;
    }
    
    /**
     * @param array  $data
     * @param string $entityClass
     *
     * @return BaseRepository
     */
    public function setEntityFromData(array $data, string $entityClass) : BaseRepository
    {
        $this->setEntity($this->dataToEntity($data, $entityClass));
        
        return $this;
    }
    
    /**
     * @param BaseEntity $entity
     *
     * @return BaseRepository
     */
    public function setEntity(BaseEntity $entity) : BaseRepository
    {
        $this->entity = $entity;
        
        return $this;
    }
    
    /**
     * @param array  $data
     * @param string $entityClass
     *
     * @param string $type
     *
     * @return BaseEntity
     */
    public function dataToEntity(array $data, string $entityClass, string $type = 'read') : BaseEntity
    {
        return $this->serializer->fromArray(
            $data,
            $entityClass,
            DeserializationContext::create()->setGroups([$type])
        );
    }
    
    /**
     * @param BaseEntity $entity
     *
     * @param string     $type
     *
     * @return array
     */
    public function entityToData(BaseEntity $entity, string $type = 'read') : array
    {
        return $this->serializer->toArray(
            $entity,
            SerializationContext::create()->setGroups([$type])
        );
    }
    
    /**
     * @return PageNavigation|null
     */
    public function getNav()
    {
        return $this->nav;
    }
    
    /**
     * @param PageNavigation $nav
     */
    public function setNav(PageNavigation $nav)
    {
        $this->nav = $nav;
    }
    
    public function clearNav()
    {
        $this->nav = null;
    }
}