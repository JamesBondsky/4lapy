<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:50
 */

namespace FourPaws\MobileApiBundle\Dto\Response;


use FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribe;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use FourPaws\PersonalBundle\Entity\OrderSubscribe as PersonalOrderSubscribe;
use JMS\Serializer\Annotation as Serializer;

class OrderSubscribeParamsResponce implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("deliveries")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribe>")
     * @var OrderSubscribe[]
     */
    protected $deliveries;

    protected $deliveryRanges;

    protected $frequency;


}