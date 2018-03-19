<?php

namespace FourPaws\SapBundle\Dto\In\StoresStock;

/**
 * Class StockItem
 *
 * @package FourPaws\SapBundle\Dto\In
 */
class StockItem
{
    /**
     * УИД торгового предложения
     *
     * @var int
     */
    protected $offerXmlId = 0;

    /**
     * Код магазина
     *
     * @var string
     */
    protected $storeCode = '';

    /**
     * Остатки
     *
     * @var float
     */
    protected $stockValue = 0;

    /**
     * @return string
     */
    public function getOfferXmlId(): string
    {
        return $this->offerXmlId;
    }

    /**
     * @param string $offerXmlId
     * @return StockItem
     */
    public function setOfferXmlId(string $offerXmlId): StockItem
    {
        $this->offerXmlId = $offerXmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        return $this->storeCode;
    }

    /**
     * @param string $storeCode
     *
     * @return StockItem
     */
    public function setStoreCode(string $storeCode): StockItem
    {
        $this->storeCode = $storeCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getStockValue(): float
    {
        return $this->stockValue;
    }

    /**
     * @param float $stockValue
     *
     * @return StockItem
     */
    public function setStockValue(float $stockValue): StockItem
    {
        $this->stockValue = $stockValue;

        return $this;
    }
}
