<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Location;

use JMS\Serializer\Annotation as Serializer;

class MetroStation
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title;
    /**
     * @Serializer\Exclude()
     * @var int
     */
    protected $lineId;

    public function __construct(int $id, string $title, int $lineId)
    {
        $this->id = $id;
        $this->title = $title;
        $this->lineId = $lineId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getLineId(): int
    {
        return $this->lineId;
    }
}
