<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * @todo assert
 * ОбъектГород
 * Class City
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class City
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id = '';

    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\SerializedName("has_metro")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $hasMetro = false;

    /**
     * @Serializer\SerializedName("path")
     * @Serializer\Type("array<string>")
     * @var array
     */
    protected $path = [];
}
