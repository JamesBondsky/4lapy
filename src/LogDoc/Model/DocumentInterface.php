<?php

namespace FourPaws\LogDoc\Model;

interface DocumentInterface
{
    /**
     * @return string|int
     */
    function getId();

    /**
     * @param string|int $value
     */
    function setId($value);

    /**
     * @return string
     */
    function getXmlId(): string;

    /**
     * @param string $value
     */
    function setXmlId(string $value);

    /**
     * @return string
     */
    function getEntity(): string;

    /**
     * @param string $value
     */
    function setEntity(string $value);

    /**
     * @return string
     */
    function getKey(): string;

    /**
     * @param string $value
     */
    function setKey(string $value);

    /**
     * @return string
     */
    function getValue(): string;

    /**
     * @param string $value
     */
    function setValue(string $value);
}
