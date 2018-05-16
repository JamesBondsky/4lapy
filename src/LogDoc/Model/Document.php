<?php

namespace FourPaws\LogDoc\Model;

use FourPaws\LogDoc\Common\AbstractObject;

class Document extends AbstractObject implements DocumentInterface
{
    /** @var string|int */
    private $id;
    /** @var string */
    private $xmlId;
    /** @var string */
    private $entity;
    /** @var string */
    private $key;
    /** @var string */
    private $value;

    /**
     * @return string|int
     */
    function getId()
    {
        return $this->id ?? '';
    }

    /**
     * @param string|int $value
     * @return Document
     */
    function setId($value): self
    {
        $this->id = $value;

        return $this;
    }

    /**
     * @return string
     */
    function getXmlId(): string
    {
        return $this->xmlId ?? '';
    }

    /**
     * @param string $value
     * @return Document
     */
    function setXmlId(string $value): self
    {
        $this->xmlId = $value;

        return $this;
    }

    /**
     * @return string
     */
    function getEntity(): string
    {
        return $this->entity ?? '';
    }

    /**
     * @param string $value
     * @return Document
     */
    function setEntity(string $value): self
    {
        $this->entity = $value;

        return $this;
    }

    /**
     * @return string
     */
    function getKey(): string
    {
        return $this->key ?? '';
    }

    /**
     * @param string $value
     * @return Document
     */
    function setKey(string $value): self
    {
        $this->key = $value;

        return $this;
    }

    /**
     * @return string
     */
    function getValue(): string
    {
        return $this->value ?? '';
    }

    /**
     * @param string $value
     * @return Document
     */
    function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
