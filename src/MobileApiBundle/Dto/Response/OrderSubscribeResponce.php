<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:50
 */

namespace FourPaws\MobileApiBundle\Dto\Response;


use FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribe;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use FourPaws\PersonalBundle\Entity\OrderSubscribe as PersonalOrderSubscribe;
use JMS\Serializer\Annotation as Serializer;

class OrderSubscribeResponce implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\SerializedName("orderSusbscribe")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribe")
     * @var OrderSubscribe
     */
    protected $orderSubscribe;

    /**
     * @param PersonalOrderSubscribe $orderSubscribeCollection
     * @throws \Exception
     */
    public function __construct(PersonalOrderSubscribe $orderSubscribe)
    {
        $this->orderSubscribe = new OrderSubscribe($orderSubscribe);
    }

    /**
     * @return OrderSubscribe
     */
    public function getOrderSubscribe(): OrderSubscribe
    {
        return $this->orderSubscribe;
    }

    /**
     * @param OrderSubscribe $orderSubscribeList
     * @return OrderSubscribeResponce
     */
    public function setOrderSubscribe(OrderSubscribe $orderSubscribe): OrderSubscribeResponce
    {
        $this->orderSubscribe = $orderSubscribe;
        return $this;
    }
}