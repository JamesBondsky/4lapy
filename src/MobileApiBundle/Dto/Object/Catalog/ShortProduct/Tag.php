<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Tag
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct
 *
 * ОбъектКаталога.КраткийТовар.Тег
 */
class Tag
{
    /**
     * Идентификатор тега
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     */
    protected $id;

    /**
     * Полный путь до изображения
     * графического представления тега
     * @Serializer\Type("string")
     * @Serializer\SerializedName("img")
     *
     * @var string
     */
    protected $img;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Tag
     */
    public function setId(int $id): Tag
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getImg(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     *
     * @return Tag
     */
    public function setImg(string $img): Tag
    {
        $this->img = $img;
        return $this;
    }
}
