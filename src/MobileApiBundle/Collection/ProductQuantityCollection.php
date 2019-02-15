<?php
/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Collection;


use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;

class ProductQuantityCollection extends ArrayCollection
{
    protected $availableProducts = [];
    protected $unAvailableProducts = [];

    /**
     * ProductQuantityCollection constructor.
     * @param ProductQuantity[] $elements
     */
    public function __construct(array $elements = [])
    {
        parent::__construct($elements);
    }

    /**
     * @param string $storeCode
     * @return ProductQuantity[]
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getAvailableInStore(string $storeCode): array
    {
        if (!$this->isAvailabilityActualized($storeCode)) {
            $this->actualizeAvailability($storeCode);
        }

        return $this->availableProducts[$storeCode];
    }

    /**
     * @param string $storeCode
     * @return ProductQuantity[]
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getUnAvailableInStore(string $storeCode): array
    {
        if (!$this->isAvailabilityActualized($storeCode)) {
            $this->actualizeAvailability($storeCode);
        }

        return $this->unAvailableProducts[$storeCode];
    }

    /**
     * @param string $storeCode
     * @return bool
     */
    protected function isAvailabilityActualized(string $storeCode): bool
    {
        return array_key_exists($storeCode, $this->availableProducts)
            && array_key_exists($storeCode, $this->unAvailableProducts);
    }

    /**
     * @param string $storeCode
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function actualizeAvailability(string $storeCode): void
    {
        $this->availableProducts[$storeCode] = [];
        $this->unAvailableProducts[$storeCode] = [];
        foreach ($this->getValues() as $product) {

            $offerId = $this->getOfferId($product);
            $quantity = $this->getQuantity($product);

            /** @var Offer $offer */
            $offer = (new OfferQuery())
                ->withFilter(['ID' => $offerId])
                ->exec()
                ->current();

            if (!$offer) {
                continue;
            }

            if ($available = $offer->getAllStocks()->getStores($quantity)->hasStoreCode($storeCode)) {
                $this->availableProducts[$storeCode][] = $product;
            } else {
                $this->unAvailableProducts[$storeCode][] = $product;
            }
        }
    }

    /**
     * @var ProductQuantity $product
     * @return int
     */
    protected function getOfferId($product)
    {
        return $product->getProductId();
    }

    /**
     * @var ProductQuantity $product
     * @return int
     */
    protected function getQuantity($product)
    {
        return $product->getQuantity();
    }

}