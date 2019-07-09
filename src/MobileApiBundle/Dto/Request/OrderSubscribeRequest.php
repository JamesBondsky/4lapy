<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:49
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribeRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Номер подписки на доставку
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $orderSubscribeId;

    /**
     * @return int
     */
    public function getOrderSubscribeId(): int
    {
        return $this->orderSubscribeId;
    }

    /**
     * @param int $orderSubscribeId
     * @return OrderStatusHistoryRequest
     */
    public function setOrderSubscribeId(int $orderSubscribeId): OrderSubscribeRequest
    {
        $this->orderSubscribeId = $orderSubscribeId;
        return $this;
    }
}