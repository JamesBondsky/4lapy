<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\AppBundle\Construction\UnserializeObjectConstructor;
use FourPaws\StoreBundle\Collection\BaseCollection;
use FourPaws\StoreBundle\Entity\Base as BaseEntity;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ValidationException;
use JMS\Serializer\DeserializationContext;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var DataManager
     */
    protected $table;

    abstract protected function getDataClass(): string;

    abstract protected function getCollectionClass(): string;

    abstract protected function getEntityClass(): string;

    abstract protected function getDefaultFilter(): array;

    abstract protected function getDefaultOrder(): array;

    /**
     * BaseRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface $validator
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;

        $dataClass = $this->getDataClass();
        $this->table = new $dataClass();
    }

    /**
     * @param BaseEntity $entity
     *
     * @return bool
     * @throws BitrixRuntimeException
     * @throws ValidationException
     */
    public function create(BaseEntity $entity): bool
    {
        $validationResult = $this->validator->validate($entity, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }

        $table = $this->table;
        try {
            $result = $table::add(
                $this->arrayTransformer->toArray($entity, SerializationContext::create()->setGroups(['create']))
            );

            if ($result->isSuccess()) {
                $entity->setId((int)$result);

                return true;
            }
            $error = $result->getErrorMessages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        throw new BitrixRuntimeException($error);
    }

    /**
     * @param int $id
     *
     * @throws ArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return BaseEntity|null
     */
    public function find(int $id)
    {
        $this->checkIdentifier($id);

        $result = $this->findBy(['ID' => $id], [], 1)->first();
        return $result instanceof BaseEntity ? $result : null;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return BaseCollection
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null
    ) {
        if (empty($orderBy)) {
            $orderBy = $this->getDefaultOrder();
        }

        $criteria = array_merge($this->getDefaultFilter(), $criteria);

        $query = $this->table::query()
            ->setSelect(['*', 'UF_*'])
            ->setFilter($criteria)
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset);

        $entities = $this->modifyQuery($query)->exec();

        $result = [];
        while ($entity = $entities->fetch()) {
            $result[$entity['ID']] = $entity;
        }

        /**
         * todo change group name to constant
         */
        $collectionClass = $this->getCollectionClass();

        return new $collectionClass(
            $this->arrayTransformer->fromArray(
                $result,
                sprintf('array<%s>', $this->getEntityClass()),
                DeserializationContext::create()->setGroups(['read'])
                    ->setAttribute(UnserializeObjectConstructor::CALL_CONSTRUCTOR, true)
            )
        );
    }

    /**
     * @param BaseEntity $entity
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function update(BaseEntity $entity): bool
    {
        $this->checkIdentifier($entity->getId());
        $validationResult = $this->validator->validate($entity, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }

        try {
            $result = $this->table::update(
                $entity->getId(),
                $this->arrayTransformer->toArray($entity, SerializationContext::create()->setGroups(['update']))
            );

            if ($result->isSuccess()) {
                return true;
            }

            $error = $result->getErrorMessages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        throw new BitrixRuntimeException($error);
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->checkIdentifier($id);

        try {
            $result = $this->table::delete($id);
            if ($result->isSuccess()) {
                return true;
            }

            $error = $result->getErrorMessages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        throw new BitrixRuntimeException($error);
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    protected function checkIdentifier(int $id): void
    {
        try {
            $result = $this->validator->validate(
                $id,
                [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1]),
                    new Type(['type' => 'integer']),
                ],
                ['delete']
            );
        } catch (ValidatorException $exception) {
            throw new ConstraintDefinitionException('Wrong constraint configuration');
        }
        if ($result->count()) {
            throw new InvalidIdentifierException(sprintf('Wrong identifier %s passed', $id));
        }
    }

    /**
     * @param Query $query
     * @return Query
     */
    protected function modifyQuery(Query $query): Query
    {
        return $query;
    }
}
