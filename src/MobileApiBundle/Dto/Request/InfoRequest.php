<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class InfoRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     * @var string
     */
    protected $cityId = '';

    /**
     * @Assert\Choice({
     *     "contacts",
     *     "about",
     *     "vacance",
     *     "letters",
     *     "news",
     *     "action",
     *     "delivery",
     *     "competition",
     *     "register_terms",
     *     "bonus_card_info",
     *     "obtain_bonus_card"
     * })
     * @Serializer\Type("string")
     * @Serializer\SerializedName("type")
     * @var string
     */
    protected $type = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("info_id")
     * @var string
     */
    protected $infoId = '';

    /**
     * @Serializer\Type("api_info_fields")
     * @Serializer\SerializedName("fields")
     * @var array
     */
    protected $fields = [];

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getInfoId(): string
    {
        return $this->infoId;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
