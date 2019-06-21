<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 18:17
 */

namespace FourPaws\MobileApiBundle\Dto\Response;


use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use JMS\Serializer\Annotation as Serializer;

class OrderSubscribeListResponce
{
    /**
     * @Serializer\SerializedName("orderSusbscribeList")
     * @Serializer\Type("array<FourPaws\PersonalBundle\Entity\OrderSubscribe>")
     * @var OrderSubscribe[]
     */
    protected $orderSubscribeList = [];

    /**
     * @param ArrayCollection $orderSubscribeCollection
     */
    public function __construct(ArrayCollection $orderSubscribeCollection)
    {
        $this->orderSubscribeList = $orderSubscribeCollection->toArray();
    }

    /**
     * @return OrderSubscribe[]
     */
    public function getOrderSubscribeList(): array
    {
        return $this->orderSubscribeList;
    }

    /**
     * @param OrderSubscribe[] $orderSubscribeList
     * @return OrderSubscribeListResponce
     */
    public function setOrderSubscribeList(array $orderSubscribeList): OrderSubscribeListResponce
    {
        $this->orderSubscribeList = $orderSubscribeList;
        return $this;
    }
}