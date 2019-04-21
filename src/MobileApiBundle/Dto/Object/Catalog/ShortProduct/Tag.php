<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\SkipWhenEmpty;

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
     * @SkipWhenEmpty
     *
     * @var string
     */
    protected $img;

    /**
     * Текст тега
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @SkipWhenEmpty
     *
     * @var string
     */
    protected $title;

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
        $this->img = (string) new FullHrefDecorator($img);
        return $this;
    }

    public function setTitle(string $title): Tag
    {
        $this->title = $title;
        return $this;
    }
}
