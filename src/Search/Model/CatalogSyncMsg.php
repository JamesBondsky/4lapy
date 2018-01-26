<?php

namespace FourPaws\Search\Model;

use JMS\Serializer\Annotation\Type;

class CatalogSyncMsg
{
    const ACTION_ADD = 'add';

    const ACTION_UPDATE = 'update';

    const ACTION_DELETE = 'delete';

    const ENTITY_TYPE_BRAND = 'brand';

    const ENTITY_TYPE_PRODUCT = 'product';

    const ENTITY_TYPE_OFFER = 'offer';

    /**
     * @var int
     * @Type("int")
     */
    protected $timestamp;

    /**
     * @var string
     * @Type("string")
     */
    protected $action = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $entityType = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $entityId = 0;

    public function __construct(string $action, string $entityType, int $entityId)
    {
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->timestamp = time();
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function withAction(string $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function withEntityId(int $entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @param string $entityType
     *
     * @return $this
     */
    public function withEntityType(string $entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForProductEntity(): bool
    {
        return self::ENTITY_TYPE_PRODUCT === $this->getEntityType();
    }

    /**
     * @return bool
     */
    public function isForOfferEntity(): bool
    {
        return self::ENTITY_TYPE_OFFER === $this->getEntityType();
    }

    /**
     * @return bool
     */
    public function isForBrandEntity(): bool
    {
        return self::ENTITY_TYPE_BRAND === $this->getEntityType();
    }

    /**
     * @return bool
     */
    public function isForAddAction(): bool
    {
        return self::ACTION_ADD === $this->getAction();
    }

    /**
     * @return bool
     */
    public function isForUpdateAction(): bool
    {
        return self::ACTION_UPDATE === $this->getAction();
    }

    /**
     * @return bool
     */
    public function isForDeleteAction(): bool
    {
        return self::ACTION_DELETE === $this->getAction();
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function withTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Сравнивает с другим объектом по значимому содержимому.
     *
     * @param CatalogSyncMsg $catalogSyncMsg
     *
     * @return bool
     */
    public function equals(CatalogSyncMsg $catalogSyncMsg)
    {
        return $catalogSyncMsg->getAction() === $this->getAction()
            && $catalogSyncMsg->getEntityType() === $this->getEntityType()
            && $catalogSyncMsg->getEntityId() === $this->getEntityId();
    }
}
