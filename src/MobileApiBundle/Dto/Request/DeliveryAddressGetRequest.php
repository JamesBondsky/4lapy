<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;

class DeliveryAddressGetRequest implements GetRequest, SimpleUnserializeRequest
{
    /**
     * Поскольку адреса доставки пользователя могут вызываться как в профиле, так и при чекауте
     * В профиле нам надо показать все адреса доставки
     * В чекауте - только для текущего выбранного города в профиле пользователя
     * Чтобы не создавать два метода - мы в один метод просто будем передавать код города пользователя (если в чекауте).
     *
     * @var string
     * @Serializer\SerializedName("cityId")
     * @Serializer\Type("string")
     */
    protected $cityCode = '';

    /**
     * @return string
     */
    public function getCityCode(): string
    {
        return $this->cityCode;
    }

    /**
     * @param string $cityCode
     * @return DeliveryAddressGetRequest
     */
    public function setCityCode(string $cityCode): DeliveryAddressGetRequest
    {
        $this->cityCode = $cityCode;
        return $this;
    }
}
