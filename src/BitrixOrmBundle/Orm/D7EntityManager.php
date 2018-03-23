<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Enum\Crud;
use FourPaws\BitrixOrmBundle\Exception\DataManagerException;
use FourPaws\BitrixOrmBundle\Exception\RuntimeException;
use FourPaws\BitrixOrmBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class D7EntityManager implements D7EntityManagerInterface
{
    /**
     * @var array
     */
    private $select = [];

    /**
     * @var array
     */
    private $filter = [];

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * @var Base
     */
    private $bitrixEntity;

    /**
     * @var string
     */
    private $primaryField;

    /**
     * @var string
     */
    private $entityClass;

    public function __construct(
        string $entityClass,
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer,
        DataManager $dataManager
    ) {
        $this->dataManager = $dataManager;
        $this->validator = $validator;
        $this->arrayTransformer = $arrayTransformer;

        $this->bitrixEntity = $this->dataManager::getEntity();
        $this->primaryField = $this->bitrixEntity->getPrimary();
        $this->entityClass = $entityClass;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->dataManager::query()
            ->setSelect($this->select)
            ->setFilter($this->filter);
    }


    /**
     * {@inheritDoc}
     */
    public function setSelect(array $select = []): void
    {
        $this->select = $select;
    }


    /**
     * {@inheritDoc}
     */
    public function setFilter(array $filter = []): void
    {
        $this->filter = $filter;
    }

    /**
     * @inheritdoc
     */
    public function findByQuery(Query $query): Collection
    {
        $result = $this->arrayTransformer->fromArray(
            $this->findByQueryRaw($query)->toArray(),
            sprintf('ArrayCollection<%s>', $this->entityClass),
            DeserializationContext::create()->setGroups([Crud::READ])
        );
        return new ArrayCollection($result);
    }

    /**
     * @inheritdoc
     */
    public function findByQueryRaw(Query $query): Collection
    {
        if ($query->getEntity()->getDataClass() !== $this->bitrixEntity->getDataClass()) {
            throw new RuntimeException('Wrong query passed to repository');
        }

        $result = $query->exec();
        if (0 === $result->getSelectedRowsCount()) {
            return new ArrayCollection();
        }
        return new ArrayCollection($result->fetchAll());
    }

    /**
     * @inheritdoc
     * @param null|int $limit
     * @param null|int $offset
     * @throws \FourPaws\BitrixOrmBundle\Exception\RuntimeException
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection {
        $query = $this
            ->getQuery()
            ->setFilter(array_merge($this->getQuery()->getFilter(), $criteria))
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset);

        return $this->findByQuery($query);
    }

    /**
     * @inheritdoc
     * @param null|int $limit
     * @param null|int $offset
     * @throws \FourPaws\BitrixOrmBundle\Exception\RuntimeException
     * @return array[]|Collection
     */
    public function findByRaw(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection {
        $query = $this
            ->getQuery()
            ->setFilter(array_merge($this->getQuery()->getFilter(), $criteria))
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset);

        return $this->findByQueryRaw($query);
    }

    /**
     * @inheritdoc
     */
    public function find($id)
    {
        return $this
            ->findByQuery($this->getQuery()->addFilter($this->primaryField, $id))
            ->first() ?: null;
    }

    /**
     * @inheritdoc
     */
    public function count(array $criteria = []): int
    {
        return $this->dataManager::getCount($criteria);
    }

    /**
     *
     * @inheritdoc
     */
    public function create($entity): bool
    {
        $this->validate($entity, Crud::CREATE);
        $data = $this->arrayTransformer->toArray(
            $entity,
            SerializationContext::create()->setGroups([Crud::CREATE])
        );
        try {
            $result = $this->dataManager::add($data);
        } catch (\Exception $e) {
            throw new DataManagerException($e->getMessage(), $e->getCode(), $e);
        }
        if ($result->isSuccess()) {
            $entity->setId($result->getId());
            return true;
        }

        throw new DataManagerException(implode(', ', $result->getErrorMessages()));
    }

    /**
     * @inheritdoc
     */
    public function update($entity): bool
    {
        $this->validate($entity, Crud::UPDATE);
        $data = $this->arrayTransformer->toArray(
            $entity,
            SerializationContext::create()->setGroups([Crud::UPDATE])
        );

        try {
            $result = $this->dataManager::update($entity->getId(), $data);
        } catch (\Exception $e) {
            throw new DataManagerException($e->getMessage(), $e->getCode(), $e);
        }
        if ($result->isSuccess()) {
            return true;
        }

        throw new DataManagerException(implode(', ', $result->getErrorMessages()));
    }

    /**
     * @inheritdoc
     */
    public function delete($id): bool
    {
        try {
            $result = $this->dataManager::delete($id);
        } catch (\Exception $e) {
            throw new DataManagerException($e->getMessage(), $e->getCode(), $e);
        }
        if ($result->isSuccess()) {
            return true;
        }

        throw new DataManagerException(implode(', ', $result->getErrorMessages()));
    }

    /**
     * @param object     $entity
     * @param string     $crudAction
     *
     * @param null|array $constraints
     *
     * @throws \FourPaws\BitrixOrmBundle\Exception\ValidationException
     */
    protected function validate($entity, string $crudAction, ?array $constraints = null): void
    {
        $validationResult = $this->validator->validate($entity, $constraints, [$crudAction]);
        if (0 !== $validationResult->count()) {
            throw new ValidationException('Wrong entity passed to ' . $crudAction);
        }
    }

    /**
     * @inheritdoc
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
