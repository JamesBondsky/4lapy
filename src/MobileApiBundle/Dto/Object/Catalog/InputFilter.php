<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use JMS\Serializer\Annotation as Serializer;

class InputFilter
{
    /**
     * Символьный код свойства в инфоблоке каталоге
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("id")
     */
    protected $id;

    /**
     * Значение свойства в инфоблоке каталога
     * Может быть string, array, null
     *
     * toDo нужно в Serializer\Type указать все возможные типы данных
     *
     * @var mixed
     * @Serializer\Type("string")
     * @Serializer\SerializedName("value")
     */
    protected $value;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return InputFilter
     */
    public function setId(string $id): InputFilter
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return InputFilter
     */
    public function setValue($value): InputFilter
    {
        $this->value = $value;
        return $this;
    }
}
