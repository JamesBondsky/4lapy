<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 19:15
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Subscribe;


class OrderSubscribe
{
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("userId")
     */
    protected $userId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("deliveryId")
     */
    protected $deliveryId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("frequency")
     */
    protected $frequency;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryTime")
     */
    protected $deliveryTime;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryPlace")
     */
    protected $deliveryPlace;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("locationId")
     */
    protected $locationId;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("active")
     */
    protected $active = true;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("orderId")
     */
    protected $orderId;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_NEXT_DEL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextDate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_EDIT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateUpdate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_LAST_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $lastCheck;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DEL_DAY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryDay;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_BONUS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $payWithbonus = false;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_CHECK_DAYS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $checkDays;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $dateCheck;

}