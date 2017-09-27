<?php

namespace FourPaws\BitrixIblockORM\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;

abstract class BitrixArrayItemBase
{
    /**
     * @var bool
     */
    protected $active = true;

    /**
     * @var int
     */
    protected $IBLOCK_ID = 0;

    /**
     * @var int
     */
    protected $ID = 0;

    /**
     * @var string
     */
    protected $CODE = '';

    /**
     * @var string
     */
    protected $XML_ID = '';

    /**
     * @var int
     */
    protected $SORT = 0;

    /**
     * @var string
     */
    protected $NAME = '';

    /**
     * @var string
     */
    protected $LIST_PAGE_URL = '';

    public function __construct(array $fields = [])
    {
        $strLenOfProperty = 9;
        $strLenOfValue = 6;

        foreach ($fields as $field => $value) {
            /**
             * Инициализация обычных полей
             */
            if (property_exists($this, $field)) {

                $this->$field = $value;

            } /**
             * Инициализация значений свойств
             * Начинается с `PROPERTY_` и заканчивается на `_VALUE`
             */
            elseif (
                strpos($field, 'PROPERTY_') === 0
                && substr($field, -$strLenOfValue) === '_VALUE'
            ) {

                $propertyCode = substr($field, $strLenOfProperty, -$strLenOfValue);
                if (property_exists($this, $propertyCode)) {
                    $this->$propertyCode = $value;
                }
            } /**
             * Инициализация пользовательских полей (используется для Section и HLBItem)
             */
            elseif ('UF_' === substr($field, 0, 3)) {
                $fieldName = substr($field, 3);
                if (property_exists($this, $fieldName)) {
                    $this->$fieldName = $value;
                }
            }
        }

        if (isset($fields['ACTIVE'])) {
            $this->withActive(BitrixUtils::bitrixBool2bool($fields['ACTIVE']));
        }
    }

    /**
     * @return int
     */
    public function getIblockId(): int
    {
        return (int)$this->IBLOCK_ID;
    }

    /**
     * @param int $iblockId
     *
     * @return $this
     */
    public function withIblockId(int $iblockId)
    {
        $this->IBLOCK_ID = $iblockId;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->ID;
    }

    /**
     * @param int $ID
     *
     * @return $this
     */
    public function withId(int $ID)
    {
        $this->ID = $ID;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->CODE;
    }

    /**
     * @param string $CODE
     *
     * @return $this
     */
    public function withCode(string $CODE)
    {
        $this->CODE = $CODE;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->XML_ID;
    }

    /**
     * @param string $XML_ID
     *
     * @return $this
     */
    public function withXmlId(string $XML_ID)
    {
        $this->XML_ID = $XML_ID;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->NAME;
    }

    /**
     * @param string $NAME
     *
     * @return $this
     */
    public function withName(string $NAME)
    {
        $this->NAME = $NAME;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return (int)$this->SORT;
    }

    /**
     * @param int $sort
     *
     * @return $this
     */
    public function withSort(int $sort)
    {
        $this->SORT = $sort;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function withActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getListPageUrl(): string
    {
        return $this->LIST_PAGE_URL;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function withListPageUrl(string $url)
    {
        $this->LIST_PAGE_URL = $url;

        return $this;
    }

}
