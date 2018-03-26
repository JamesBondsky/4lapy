<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use JMS\Serializer\Annotation\SkipWhenEmpty;
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
     * @var ValidatorInterface
     */
    protected $validator;

    /** @var BaseEntity $entity */
    protected $entity;

    /** @var null|PageNavigation */
    protected $nav;

    /** @var ArrayTransformerInterface $arrayTransformer */
    protected $arrayTransformer;

    protected $entityClass = self::class;

    /**
     * @var DataManager
     */
    private $dataManager;

    private $fileList;

    /**
     * BaseRepository constructor.
     *
     * @param ValidatorInterface        $validator
     *
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(ValidatorInterface $validator, ArrayTransformerInterface $arrayTransformer)
    {
        $this->validator = $validator;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     * @throws \Exception
     */
    public function create(): bool
    {
        if (!($this->entity instanceof BaseEntity)) {
            throw new BitrixRuntimeException('empty entity');
        }
        $validationResult = $this->validator->validate($this->entity, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }

        $data = $this->arrayTransformer->toArray($this->entity, SerializationContext::create()->setGroups(['create']));
        $data = $this->afterCreateArrayTransformer($data);

        $this->fixFileData($data);

        $res = $this->dataManager::add(
            $data
        );
        $this->clearFileList();
        if ($res->isSuccess()) {
            $this->entity->setId($res->getId());

            return true;
        }

        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()));
    }

    /**
     * @return array
     */
    public function getFileList(): array
    {
        return $this->fileList ?? [];
    }

    /**
     * @param array $fileList
     *
     * @return BaseRepository
     */
    public function setFileList(array $fileList): BaseRepository
    {
        $this->fileList = $fileList;

        return $this;
    }

    /**
     * @throws InvalidIdentifierException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     * @throws \Exception
     */
    public function update(): bool
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

        $data = $this->arrayTransformer->toArray($this->entity, SerializationContext::create()->setGroups(['update']));
        $data = $this->afterUpdateArrayTransformer($data);
        $this->fixFileData($data);

        $res = $this->dataManager::update(
            $this->entity->getId(),
            $data
        );
        $this->clearFileList();
        if ($res->isSuccess()) {
            return true;
        }
        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()));
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $this->checkIdentifier($id);
        $res = $this->dataManager::delete($id);
        if ($res->isSuccess()) {
            return true;
        }

        throw new BitrixRuntimeException(implode(', ', $res->getErrorMessages()), $id ?: null);
    }

    /**
     * можно передавать сформированный объект DataManager, можно массив
     * [
     *      'select'=>array
     *      'filter'=>array
     *      'order'=>array
     *      'limit'=>int
     *      'offset'=>int
     *      'ttl'=>int
     *      'group'=>array
     *      'runtime'=>array
     *      'countTotal'=>bool
     * ]
     *
     * @param array|DataManager $params
     *
     * @return ArrayCollection
     * @throws ObjectPropertyException
     */
    public function findBy($params): ArrayCollection
    {
        if ($params instanceof DataManager) {
            $query = $params;
        } else {
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
            if (!empty($params['countTotal'])) {
                $query->countTotal($params['countTotal']);
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
        }
        if ($this->nav instanceof PageNavigation) {
            $query->setOffset($this->nav->getOffset());
            $query->setLimit($this->nav->getLimit());
            $query->countTotal(true);
        }
        $result = $query->exec();

        if ($this->nav instanceof PageNavigation) {
            $this->nav->setRecordCount($result->getCount());
        }

        if (0 === $result->getSelectedRowsCount()) {
            return new ArrayCollection();
        }

        if (!empty($params['setKey'])) {
            $allItems = [];
            $i = -1;
            while ($item = $result->fetch()) {
                $i++;
                $allItems[$item[$params['setKey']] ?? 'key_' . $i] = $item;
            }
        } else {
            $allItems = $result->fetchAll();
        }
        if (!empty($params['entityClass'])) {
            $entityClass = $params['entityClass'];
        }
        if (empty($entityClass) && !empty($this->getEntityClass())) {
            $entityClass = $this->getEntityClass();
        }
        if (!empty($entityClass)) {
            return new ArrayCollection($this->arrayTransformer->fromArray(
                $allItems,
                sprintf('array<%s>', $entityClass),
                DeserializationContext::create()->setGroups(['read'])
            ));
        }

        return new ArrayCollection($allItems);
    }

    /**
     * @param int $id
     *
     * @return BaseEntity
     * @throws ObjectPropertyException
     * @throws NotFoundException
     */
    public function findById(int $id): BaseEntity
    {
        $result = $this->findBy(['filter' => ['ID' => $id]]);
        if ($result->isEmpty()) {
            throw new NotFoundException('Entity not found');
        }

        return $result->first();
    }

    /**
     * @param array $filter
     *
     * @throws ObjectPropertyException
     * @return int
     */
    public function getCount(array $filter = []): int
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
    public function setDataManager(DataManager $dataManager): BaseRepository
    {
        $this->dataManager = $dataManager;

        return $this;
    }

    /**
     * @return null|DataManager
     */
    public function getDataManager()
    {
        return $this->dataManager;
    }

    /**
     * @param array  $data
     * @param string $entityClass
     *
     * @return BaseRepository
     * @throws EmptyEntityClass
     */
    public function setEntityFromData(array $data, string $entityClass = ''): BaseRepository
    {
        $this->setEntity($this->dataToEntity($data, $entityClass));

        return $this;
    }

    /**
     * @param BaseEntity $entity
     *
     * @return BaseRepository
     */
    public function setEntity(BaseEntity $entity): BaseRepository
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
     * @return BaseEntity|array
     * @throws EmptyEntityClass
     */
    public function dataToEntity(array $data, string $entityClass = '', string $type = 'read')
    {
        if (empty($entityClass)) {
            $entityClass = $this->getEntityClass();
        }
        if (empty($entityClass)) {
            throw new EmptyEntityClass('Не указан класс, преобразование невозможно');
        }
        return $this->arrayTransformer->fromArray(
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
    public function entityToData(BaseEntity $entity, string $type = 'read'): array
    {
        return $this->arrayTransformer->toArray(
            $entity,
            SerializationContext::create()->setGroups([$type])
        );
    }

    /**
     * @return null|PageNavigation
     */
    public function getNav(): ?PageNavigation
    {
        return $this->nav;
    }

    /**
     * @param PageNavigation $nav
     */
    public function setNav(PageNavigation $nav): void
    {
        $this->nav = $nav;
    }

    /**
     *
     */
    public function clearNav(): void
    {
        $this->nav = null;
    }

    /**
     * @param array $file
     *
     * @return BaseRepository
     */
    public function addFileList(array $file = []): BaseRepository
    {
        $this->fileList[key($file)] = current($file);

        return $this;
    }

    public function clearFileList(): void
    {
        $this->fileList = null;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
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

    /** fix для сохранения файлов,
     *
     * @param $data
     */
    private function fixFileData(&$data): void
    {
        $fileList = $this->getFileList();
        if (!empty($fileList)) {
            foreach ($fileList as $code => $file) {
                if (\array_key_exists($code, $data) && (int)$data[$code] === 1) {
                    $data[$code] = $file;
                }
            }
        }
    }

    /**
     * Дополнительная обработка массива перед отправкой в базу
     * методом create
     *
     * @param array $data
     * @return array
     */
    protected function afterCreateArrayTransformer(array $data): array
    {
        return $data;
    }

    /**
     * Дополнительная обработка массива перед отправкой в базу
     * методом update
     *
     * @param array $data
     * @return array
     */
    protected function afterUpdateArrayTransformer(array $data): array
    {
        return $data;
    }
}
