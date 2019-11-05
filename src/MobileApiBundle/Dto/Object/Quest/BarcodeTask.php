<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

class BarcodeTask
{
    public const SCAN_ERROR = 0;
    public const INCORRECT_PRODUCT = 1;
    public const SUCCESS_SCAN = 2;

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\SerializedName("task")
     * @Serializer\Type("string")
     * @var string
     */
    protected $task = '';

    /**
     * @Serializer\SerializedName("image")
     * @Serializer\Type("string")
     * @var string
     */
    protected $image = '';

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return BarcodeTask
     */
    public function setTitle(string $title): BarcodeTask
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @param string $task
     * @return BarcodeTask
     */
    public function setTask(string $task): BarcodeTask
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return BarcodeTask
     */
    public function setImage(?string $image): BarcodeTask
    {
        $this->image = (string) new FullHrefDecorator($image);
        return $this;
    }
}
