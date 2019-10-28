<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектРасчетЗаказа
 * Class OrderCalculate
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class OrderCalculate
{
    /**
     * ОбъектЦена. Финальная стоимость заказа (чека)
     * @Serializer\SerializedName("total_price")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @var Price
     */
    protected $totalPrice;
    
    /**
     * Купон
     * @Serializer\SerializedName("personal_offer")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Coupon")
     * @Serializer\SkipWhenEmpty()
     * @var Coupon
     */
    protected $coupon;
    
    /**
     * Расшифровка финальной стоимости, список ОбъектДетализации[]
     * @Serializer\SerializedName("price_details")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Detailing>")
     * @var Detailing[]
     */
    protected $priceDetails = [];

    /**
     * Расшифровка движений по карте клиента на текущий заказ, список ОбъектДетализации[]
     * @Serializer\SerializedName("card_details")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Detailing>")
     * @var Detailing[]
     */
    protected $cardDetails = [];
    
    /**
     * Еасть ли активные купоны
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("has_coupons")
     * @var bool
     */
    protected $hasCoupons = false;

    /**
     * Расшифровка движений марок клиентов на текущий заказ, список ОбъектДетализацииМарок[]
     * @Serializer\SerializedName("stamps_details")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\StampsDetailing>")
     * @Serializer\SkipWhenEmpty()
     * @var StampsDetailing[]
     */
    protected $stampsDetails = [];

    /**
     * Можно ли связаться с клиентом по телефону
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isPhoneCallAvailable")
     * @Serializer\SkipWhenEmpty()
     * @var bool
     */
    protected $isPhoneCallAvailable = true;

    /**
     * @return Price
     */
    public function getTotalPrice(): Price
    {
        return $this->totalPrice;
    }

    /**
     * @param Price $totalPrice
     *
     * @return OrderCalculate
     */
    public function setTotalPrice(Price $totalPrice): OrderCalculate
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * @return Detailing[]
     */
    public function getPriceDetails(): array
    {
        return $this->priceDetails;
    }
    
    /**
     * Используемый промокод
     * @Serializer\Type("string")
     * @Serializer\SerializedName("promocode_result")
     * @var string
     */
    protected $promoCodeResult = '';

    /**
     * @param Detailing[] $priceDetails
     *
     * @return OrderCalculate
     */
    public function setPriceDetails(array $priceDetails): OrderCalculate
    {
        $this->priceDetails = $priceDetails;
        return $this;
    }

    /**
     * @return Detailing[]
     */
    public function getCardDetails(): array
    {
        return $this->cardDetails;
    }

    /**
     * @param Detailing[] $cardDetails
     *
     * @return OrderCalculate
     */
    public function setCardDetails(array $cardDetails): OrderCalculate
    {
        $this->cardDetails = $cardDetails;
        return $this;
    }

    /**
     * @return StampsDetailing[]
     */
    public function getStampsDetails(): array
    {
        return $this->stampsDetails;
    }

    /**
     * @param StampsDetailing[] $stampsDetails
     *
     * @return OrderCalculate
     */
    public function setStampsDetails(array $stampsDetails): OrderCalculate
    {
        $this->stampsDetails = $stampsDetails;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getHasCoupons(): bool
    {
        return $this->hasCoupons;
    }
    
    
    /**
     * @param bool $hasCoupons
     *
     * @return OrderCalculate
     */
    public function setHasCoupons(bool $hasCoupons): OrderCalculate
    {
        $this->hasCoupons = $hasCoupons;
        return $this;
    }

    /**
     * @param bool $isPhoneCallAvailable
     * @return OrderCalculate
     */
    public function setIsPhoneCallAvailable(bool $isPhoneCallAvailable): OrderCalculate
    {
        $this->isPhoneCallAvailable = $isPhoneCallAvailable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPhoneCallAvailable(): bool
    {
        return $this->isPhoneCallAvailable;
    }
    
    /**
     * @return Coupon
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }
    
    /**
     * @param Coupon $coupon
     *
     * @return OrderCalculate
     */
    public function setCoupon(Coupon $coupon): OrderCalculate
    {
        $this->coupon = $coupon;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPromoCodeResult(): string
    {
        return $this->promoCodeResult;
    }
    
    /**
     * @param string $promoCodeResult
     *
     * @return OrderCalculate
     */
    public function setPromoCodeResult(string $promoCodeResult): OrderCalculate
    {
        $this->promoCodeResult = $promoCodeResult;
        return $this;
    }
}
