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
     * Расшифровка движений марок клиентов на текущий заказ, список ОбъектДетализацииМарок[]
     * @Serializer\SerializedName("stamps_details")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\StampsDetailing>")
     * @var StampsDetailing[]
     */
    protected $stampsDetails = [];

    /**
     * Используемый промокод
     * @Serializer\Type("string")
     * @Serializer\SerializedName("promocode_result")
     * @var string
     */
    protected $promoCodeResult = '';

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
