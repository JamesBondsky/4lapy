<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class PickupResult extends BaseResult implements PickupResultInterface
{
    /** @var StoreCollection */
    protected $bestShops;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    protected function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();
        /**
         * Если склад является магазином, то учитываем его график работы
         */
        if ($this->selectedStore && $this->selectedStore->isShop()) {
            $this->calculateWithStoreSchedule($this->deliveryDate, $this->selectedStore);
        }

        if ((null !== $this->fullstockResult) &&
            (!$this->stockResult || !$this->stockResult->getUnavailable()->isEmpty())
        ) {
            $this->addError(new Error('Присутствуют товары не в наличии'));
        }
    }

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
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom();
    }

    /**
     * @return Store
     */
    public function getSelectedShop(): Store
    {
        return $this->getSelectedStore();
    }

    /**
     * @param Store $selectedStore
     *
     * @return PickupResultInterface
     */
    public function setSelectedShop(Store $selectedStore): PickupResultInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->setSelectedStore($selectedStore);
    }

    /**
     * @return StoreCollection
     */
    public function getBestShops(): StoreCollection
    {
        if (null === $this->bestShops) {
            $this->bestShops = $this->doGetBestStores();
        }

        return $this->bestShops;
    }/** @noinspection SenselessProxyMethodInspection */

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     * @throws ApplicationCreateException
     */
    protected function checkIsDeliverable(Offer $offer): bool
    {
        return parent::checkIsDeliverable($offer) && $offer->getProduct()->isPickupAvailable();
    }

    /**
     * @param bool $withHtmlTags
     * @return string
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     */
    public function getTextForOffer($withHtmlTags = true): string
    {
        if ($this->getDeliveryCode() === DeliveryService::INNER_PICKUP_CODE) {
            $shopCount = $this->getShopCount();
            $totalCount = $shopCount['TOTAL'];
            $availableCount = $shopCount['AVAILABLE'];
            $hasToday = $shopCount['HAS_TODAY'];
            $unavailableCount = $shopCount['TOTAL'] - $shopCount['AVAILABLE'];
            if ($availableCount) {
                if ($hasToday) {
                    $text = 'из ' . $availableCount . ' ' . WordHelper::declension(
                        (int)$availableCount,
                        [
                            'магазина',
                            'магазинов',
                            'магазинов',
                        ]
                    );
                    $text .= ' ' . DeliveryTimeHelper::showByDate($this->getDeliveryDate(), 0, [
                        'DATE_FORMAT' => 'XX',
                        'SHOW_TIME'   => $hasToday,
                    ]);
                } else {
                    $text = DeliveryTimeHelper::showByDate($this->getDeliveryDate(), 0, [
                        'DATE_FORMAT' => 'XX',
                        'SHOW_TIME'   => $hasToday,
                    ]) . ' из ' . $availableCount . ' ' . WordHelper::declension(
                        (int)$availableCount,
                        [
                            'магазина',
                            'магазинов',
                            'магазинов',
                        ]
                    );
                }
                if ($unavailableCount) {
                    if ($withHtmlTags) {
                        $text .= '<br>';
                    }
                    $text .=  ' и из ' . $unavailableCount . ' ' . WordHelper::declension(
                        (int)$unavailableCount,
                        [
                            'магазина',
                            'магазинов',
                            'магазинов',
                        ]
                    ) . ' позже';
                }
            } else {
                $text = 'из ' .  $totalCount . ' ' . WordHelper::declension(
                    (int)$totalCount,
                    [
                        'магазина',
                        'магазинов',
                        'магазинов',
                    ]
                ) . ' ' . DeliveryTimeHelper::showByDate($this->getDeliveryDate(), 0, [
                    'DATE_FORMAT' => 'XX',
                    'SHOW_TIME'   => false,
                ]);
            }
        } else {
            $text = DeliveryTimeHelper::showByDate($this->getDeliveryDate(), 0, ['DATE_FORMAT' => 'XX']);
        }
        return $text;
    }

    /**
     * Изменяет дату доставки в соответствии с графиком работы магазина
     *
     * @param \DateTime $date
     * @param Store     $store
     */
    protected function calculateWithStoreSchedule(\DateTime $date, Store $store): void
    {
        $schedule = $store->getSchedule();
        $hour = (int)$date->format('G') + 1;
        if ($hour <= $schedule->getFrom()) {
            $date->setTime($schedule->getFrom() + 1, 0);
        } elseif ($schedule->getTo() && $hour >= ($schedule->getTo() - 1)) {
            $date->modify('+1 day');
            $date->setTime($schedule->getFrom() + 1, 0);
        } else {
            $date->modify('+1 hour');
        }
    }

    /**
     * @param PickupResultInterface $pickup
     *
     * @return int[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getShopCount(): array
    {
        $shops = $this->getBestShops();
        $pickup = clone $this;

        $countTotal = 0;
        $hasToday = false;
        $countFirst = 0;
        $firstDate = null;
        $currentDate = new \DateTime();
        /** @var Store $shop */
        foreach ($shops as $shop) {
            $pickup->setSelectedStore($shop);
            if (!$pickup->isSuccess()) {
                break;
            }

            if (abs($pickup->getDeliveryDate()->getTimestamp() - $currentDate->getTimestamp()) < 2 * 3600) {
                $hasToday = true;
                $countFirst++;
            }

            if (!$hasToday) {
                if (null === $firstDate) {
                    $firstDate = $pickup->getDeliveryDate();
                }
                if ($pickup->getDeliveryDate()->format('z') === $firstDate->format('z')) {
                    $countFirst++;
                }
            }
            $countTotal++;
        }

        return [
            'AVAILABLE' => $countFirst,
            'HAS_TODAY' => $hasToday,
            'TOTAL'     => $countTotal,
        ];
    }
}
