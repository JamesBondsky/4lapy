<?php

namespace FourPaws\BitrixIblockORM\Model;

abstract class BitrixArrayItemBase
{
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
            }
        }
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



}
