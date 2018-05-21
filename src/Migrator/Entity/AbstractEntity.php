<?php

namespace FourPaws\Migrator\Entity;

use Exception;

/**
 * Class AbstractEntity
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class AbstractEntity implements EntityInterface
{
    protected $entity;

    protected $provider;

    abstract public function setDefaults(): array;

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function checkEntity()
    {
        return EntityTable::getList([
                'select' => ['ENTITY'],
                'filter' => ['ENTITY' => $this->entity],
                'limit' => 1,
            ])->getSelectedRowsCount() === 1;
    }

    /**
     * By default - ID
     *
     * @return string
     */
    public function getPrimary(): string
    {
        return 'ID';
    }

    /**
     * By default - TIMESTAMP_X
     *
     * @return string
     */
    public function getTimestamp(): string
    {
        return 'TIMESTAMP_X';
    }

    /**
     * AbstractEntity constructor.
     *
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string $primary
     * @param array $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     */
    abstract public function addItem(string $primary, array $data): AddResult;

    /**
     * @param string $primary
     * @param array $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    abstract public function updateItem(string $primary, array $data): UpdateResult;

    /**
     * @param string $primary
     * @param array $item
     *
     * @return Result
     *
     * @throws Exception
     */
    public function addOrUpdateItem(string $primary, array $item): Result
    {
        try {
            if (MapTable::isInternalEntityExists($primary, $this->entity)) {
                $result = $this->updateItem(MapTable::getInternalIdByExternalId($primary, $this->entity), $item);
            } else {
                $result = $this->addItem($primary, $item);
            }
        } catch (Exception $e) {
            /** @noinspection ThrowRawExceptionInspection */
            throw new Exception(sprintf('Error add or update entity %s with primary %s: %s', $this->entity, $primary, $e->getMessage()));
        }

        return $result;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function getPrimaryByItem(array $item): string
    {
        return $item[$this->getPrimary()];
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function getTimestampByItem(array $item): string
    {
        return $item[$this->getTimestamp()] ?? '';
    }

    /**
     * @param array $data
     * @param string $internal
     * @param string $entity
     */
    public function setInternalKeys(array $data, string $internal, string $entity): void
    {
        /**
         * Заглушка
         */
    }
}
