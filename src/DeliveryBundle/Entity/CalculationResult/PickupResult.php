<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class PickupResult extends BaseResult implements PickupResultInterface
{
    /** @var StoreCollection */
    protected $bestShops;

    protected function doCalculatePeriod(): void
    {
        parent::doCalculatePeriod();
        $days = $this->deliveryDate->diff($this->getCurrentDate())->days;
        if ($days === 0) {
            $hours = $this->deliveryDate->diff($this->getCurrentDate())->h;
            $this->setPeriodFrom($hours >= 1 ? $hours : 1);
            $this->setPeriodType(self::PERIOD_TYPE_HOUR);
        }
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom();
    }

    /**
     * @return Store
     * @throws NotFoundException
     */
    public function getSelectedStore(): Store
    {
        if (null === $this->selectedStore) {
            $this->selectedStore = $this->getBestShops()->first();
        }

        return $this->selectedStore;
    }

    /**
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getBestShops(): StoreCollection
    {
        if (null === $this->bestShops) {
            $this->bestShops = $this->doGetBestShops();
        }

        return $this->bestShops;
    }/** @noinspection SenselessProxyMethodInspection */

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @return StoreCollection
     * @throws NotFoundException
     */
    protected function doGetBestShops(): StoreCollection
    {
        $shops = $this->getStockResult()->getStores();
        $storeData = [];
        /** @var Store $shop */
        foreach ($shops as $shop) {
            $tmpPickup = (clone $this)->setStockResult($this->getStockResult()->filterByStore($shop));
            $storeData[$shop->getXmlId()] = [
                'RESULT' => $tmpPickup,
                'AVAILABLE_PRICE' => $tmpPickup->getStockResult()->getAvailable()->getPrice()
            ];
        }

        /**
         * 1) По убыванию % от суммы товаров заказа в наличии в магазине
         * 2) По возрастанию даты готовности заказа к выдаче
         * 3) По адресу магазина в алфавитном порядке
         */
        /**
         * @param Store $shop1
         * @param Store $shop2
         * @return int
         * @throws NotFoundException
         * @throws \Bitrix\Main\ArgumentException
         * @throws \FourPaws\App\Exceptions\ApplicationCreateException
         * @throws StoreNotFoundException
         */
        $sortFunc = function (Store $shop1, Store $shop2) use ($storeData) {
            /** @var array $shopData1 */
            $shopData1 = $storeData[$shop1->getXmlId()];
            /** @var array $shopData2 */
            $shopData2 = $storeData[$shop2->getXmlId()];

            if ($shopData1['AVAILABLE_PRICE'] !== $shopData2['AVAILABLE_PRICE']) {
                return ($shopData1['AVAILABLE_PRICE'] > $shopData2['AVAILABLE_PRICE']) ? -1 : 1;
            }

            /** @var PickupResult $result1 */
            $result1 = $shopData1['RESULT'];
            /** @var PickupResult $result2 */
            $result2 = $shopData1['RESULT'];

            /** в начало переносим магазины с доступным самовывозом */
            if ($result1->isSuccess() !== $result2->isSuccess()) {
                return $result1->isSuccess() > $result2->isSuccess() ? -1 : 1;
            }

            $deliveryDate1 = $result1->getDeliveryDate();
            $deliveryDate2 = $result2->getDeliveryDate();


            if ($deliveryDate1 !== $deliveryDate2) {
                return ($deliveryDate1 > $deliveryDate2) ? 1 : -1;
            }

            return $shop1->getAddress() > $shop2->getAddress() ? 1 : -1;
        };

        $iterator = $shops->getIterator();
        $iterator->uasort($sortFunc);

        return new StoreCollection(iterator_to_array($iterator));
    }

    protected function resetResult(): void
    {
        $this->bestShops = null;
        parent::resetResult();
    }
}
