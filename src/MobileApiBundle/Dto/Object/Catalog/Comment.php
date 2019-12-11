<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use JMS\Serializer\Annotation as Serializer;


/**
 * ОбъектОтзывы
 *
 * Class Comment
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog
 */
class Comment
{
    /**
     * Дата отзыва.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("date")
     */
    protected $date;
    
    /**
     * Оценка отзыва (количество звезд).
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("stars")
     */
    protected $stars;
    
    /**
     * Текст отзыва.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("text")
     */
    protected $text;
    
    /**
     * Картинки, прикрепоенные к отзыву.
     *
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("images")
     */
    protected $images;
    
    /**
     * Автор отзыва.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("author")
     */
    protected $author;
  
    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }
    
    /**
     * @param string
     *
     * @return Comment
     */
    public function setDate(string $date): Comment
    {
        $this->date = $date;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getStars(): int
    {
        return $this->stars;
    }
    
    /**
     * @param int
     *
     * @return Comment
     */
    public function setStars(int $stars): Comment
    {
        $this->stars = $stars;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->stars;
    }
    
    /**
     * @param string
     *
     * @return Comment
     */
    public function setText(string $stars): Comment
    {
        $this->stars = $stars;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }
    
    /**
     * @param array
     *
     * @return Comment
     */
    public function setImages(array $images): Comment
    {
        $this->images = $images;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }
    
    /**
     * @param string
     *
     * @return Comment
     */
    public function setAuthor(string $author): Comment
    {
        $this->author = $author;
        return $this;
    }
}
