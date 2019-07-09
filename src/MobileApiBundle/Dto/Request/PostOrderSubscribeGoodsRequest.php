<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.06.2019
 * Time: 13:23
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribeItem;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PostOrderSubscribeGoodsRequest implements SimpleUnserializeRequest, PostRequest
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
     * Товары в подписке
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe\OrderSubscribeItem>")
     * @var OrderSubscribeItem[]
     */
    protected $goods = [];

    /**
     * @return int
     */
    public function getOrderSubscribeId(): int
    {
        return $this->orderSubscribeId;
    }

    /**
     * @param int $orderSubscribeId
     * @return PostOrderSubscribeGoodsRequest
     */
    public function setOrderSubscribeId(int $orderSubscribeId): PostOrderSubscribeGoodsRequest
    {
        $this->orderSubscribeId = $orderSubscribeId;
        return $this;
    }

    /**
     * @return OrderSubscribeItem[]
     */
    public function getGoods(): array
    {
        return $this->goods;
    }

    /**
     * @param OrderSubscribeItem[] $goods
     * @return PostOrderSubscribeGoodsRequest
     */
    public function setGoods(array $goods): PostOrderSubscribeGoodsRequest
    {
        $this->goods = $goods;
        return $this;
    }
}