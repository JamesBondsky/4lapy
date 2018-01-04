<?php

namespace FourPaws\AppBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
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
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;
    
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    
    /** @var BaseEntity $entity */
    protected $entity;
    
    /**
     * @var DataManager
     */
    private $dataManager;
    
    /**
     * AddressRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface        $validator
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator        = $validator;
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
            $this->arrayTransformer->toArray($this->entity, SerializationContext::create()->setGroups(['create']))
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
        $validationResult = $this->validator->validate($this->entity, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }
        
        $res = $this->dataManager::update(
            $this->entity->getId(),
            $this->arrayTransformer->toArray($this->entity, SerializationContext::create()->setGroups(['update']))
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
     * @return array
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
        $result = $query->exec();
        if (0 === $result->getSelectedRowsCount()) {
            return [];
        }
        
        $allItems = $result->fetchAll();
        if (!empty($params['entityClass'])) {
            return $this->arrayTransformer->fromArray(
                $allItems,
                sprintf('array<%s>', $params['entityClass']),
                DeserializationContext::create()->setGroups(['read'])
            );
        }
        
        return $allItems;
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
     * @return BaseEntity
     */
    public function dataToEntity(array $data, string $entityClass) : BaseEntity
    {
        return $this->arrayTransformer->fromArray(
            $data,
            $entityClass,
            DeserializationContext::create()->setGroups(['read'])
        );
    }
    
    /**
     * @param string $entityClass
     *
     * @return array
     */
    public function entityToData(string $entityClass) : array
    {
        return $this->arrayTransformer->toArray(
            $entityClass,
            DeserializationContext::create()->setGroups(['read'])
        );
    }
}