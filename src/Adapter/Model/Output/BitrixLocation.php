<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.18
 * Time: 9:20
 */

namespace FourPaws\Adapter\Model\Output;

use JMS\Serializer\Annotation as Serializer;

class BitrixLocation
{
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $id;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CODE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $code;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $name;

}