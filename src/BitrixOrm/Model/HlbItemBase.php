<?php

namespace FourPaws\BitrixOrm\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;
use FourPaws\BitrixOrm\Model\Interfaces\ItemInterface;
use FourPaws\BitrixOrm\Model\Interfaces\ToArrayInterface;

abstract class HlbItemBase implements ItemInterface, ToArrayInterface
{
    /**
     * @var int
     */
    protected $ID = 0;

    /**
     * @var string
     */
    protected $UF_NAME = '';

    /**
     * @var int
     */
    protected $UF_SORT = 500;

    /**
     * @var string
     */
    protected $UF_XML_ID = '';

    public function __construct(array $fields = [])
    {
        foreach ($fields as $field => $value) {
            if (($value || $value !== null) && property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return $this->ID;
    }

    /**
     * @inheritdoc
     */
    public function withId(int $id): ItemInterface
    {
        $this->ID = $id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->UF_NAME;
    }

    /**
     * @inheritdoc
     */
    public function withName(string $name): ItemInterface
    {
        $this->UF_NAME = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSort(): int
    {
        return (int)$this->UF_SORT;
    }

    /**
     * @inheritdoc
     */
    public function withSort(int $sort): ItemInterface
    {
        $this->UF_SORT = $sort;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getXmlId(): string
    {
        return $this->UF_XML_ID;
    }

    /**
     * @inheritdoc
     */
    public function withXmlId(string $xmlId): ItemInterface
    {
        $this->UF_XML_ID = $xmlId;

        return $this;
    }


    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $field => $value) {
            if ($field === 'UF_ACTIVE' && \is_bool($value)) {
                $value = BitrixUtils::bool2BitrixBool($value);
            }
            if (!\is_object($value) && null !== $value) {
                $result[$field] = $value;
            }
        }
        return $result;
    }
}
