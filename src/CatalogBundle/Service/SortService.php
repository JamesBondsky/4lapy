<?php

namespace FourPaws\CatalogBundle\Service;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Service\StoreService;

class SortService
{
    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var array
     */
    protected $availabilitySortScript;

    /**
     * SortService constructor.
     *
     * @param DeliveryService $deliveryService
     * @param StoreService    $storeService
     */
    public function __construct(
        DeliveryService $deliveryService,
        StoreService $storeService
    )
    {
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
    }

    /**
     * @param string $activeSortCode
     * @param bool   $isQuery
     *
     * @return SortsCollection
     */
    public function getSorts(string $activeSortCode, bool $isQuery = false): SortsCollection
    {
        $sorts = $this->getBaseSorts();

        /**
         * При поиске по строке учитываем релевантность
         */
        if ($isQuery) {
            array_unshift($sorts, $this->getRelevantSort());
        }

        return new SortsCollection($sorts, $activeSortCode);
    }

    /**
     * @return Sorting
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    public function getPopularSort(): Sorting
    {
        return (new Sorting())
            ->withValue('popular')
            ->withName('популярности')
            ->withRule([
                '_script' => $this->getAvaliabilitySort(),
                'SORT'    => ['order' => 'asc'],
                '_score'  => ['order' => 'desc'],
                'ID'      => ['order' => 'desc']
            ]);
    }

    /**
     * @return Sorting
     */
    protected function getRelevantSort(): Sorting
    {
        return (new Sorting())
            ->withValue('relevance')
            ->withName('релевантности')
            ->withRule(['_score']);
    }

    /**
     * @param bool $isAsc
     *
     * @return Sorting
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    public function getPriceSort($isAsc = true): Sorting
    {
        return (new Sorting())
            ->withValue(\sprintf(
                '%s-price',
                $isAsc ? 'up' : 'down'
            ))
            ->withName(\sprintf(
                '%s цены',
                $isAsc ? 'возрастанию' : 'убыванию'
            ))
            ->withRule(
                [
                    '_script' => $this->getAvaliabilitySort(),
                    'offers.price' => [
                        'order'       => $isAsc ? 'asc' : 'desc',
                        'mode'        => 'max',
                        //ибо по умолчанию выбирается максимальная фасовка
                        'nested_path' => 'offers',
                    ],
                    //  UP
                    //                        'offers.prices.PRICE' => [
                    //                            'order'         => 'asc',
                    //                            'mode'          => 'min',
                    //                            'nested_path'   => 'offers.prices',
                    //                            'nested_filter' => [
                    //                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                    //                            ],
                    //                        ],
                    // DOWN
                    //                        'offers.prices.PRICE' => [
                    //                            'order'         => 'desc',
                    //                            'mode'          => 'max',
                    //                            'nested_path'   => 'offers.prices',
                    //                            'nested_filter' => [
                    //                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                    //                            ],
                    //                        ],
                    '_score'       => ['order' => 'desc']
                ]
            );
    }

    /**
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    protected function getBaseSorts(): array
    {
        //$currentRegionCode = $this->locationService->getCurrentRegionCode();
        //        $currentRegionCode = LocationService::DEFAULT_REGION_CODE;

        return [
            $this->getPopularSort(),
            $this->getPriceSort(),
            $this->getPriceSort(false),
        ];
    }

    /**
     * @return array
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    protected function getAvaliabilitySort(): array
    {
        if (null === $this->availabilitySortScript) {
            $deliveries = $this->deliveryService->getByLocation();

            $availableXmlIds = ['DC01'];
            foreach ($deliveries as $delivery) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $availableXmlIds = \array_merge($availableXmlIds, $this->deliveryService->getStoresByDelivery($delivery)
                                                                                        ->getXmlIds());
            }
            /**
             * вызов array_values() необходим для того, чтобы ключи шли подряд.
             * Иначе элемент придет в эластик в виде объекта, а не массива
             */
            $availableXmlIds = \array_values(
                \array_unique($availableXmlIds)
            );

            $supplierXmlIds = $this->storeService->getSupplierStores()->getXmlIds();

            $this->availabilitySortScript = [
                'type'   => 'number',
                'script' => [
                    'lang'   => 'painless',
                    'source' => '
                    int[] results = new int[20];
                    int i = 0;
                    def offers = params._source.offers;
                    for (offer in offers) {
                        int result = 0;
                        for (store in offer.availableStores) {
                            for (int j = 0; j < params.supplier.length; j++) {
                                if (store == params.supplier[j]) {
                                    result = 1;
                                }
                            }
                        }
                        if (result == 0) {
                            for (store in offer.availableStores) {
                                for (int j = 0; j < params.available.length; j++) {
                                    if (store == params.available[j]) {
                                        result = 2;
                                    }
                                }
                            }
                        }
                        i++;
                        results[i] = result;
                    }

                    int max = 0;
                    for (int k = 0; k < results.length; k++) {
                        if (results[k] > max) {
                            max = results[k];
                        }
                    }
                    return max;
                ',
                    'params' => [
                        'available' => $availableXmlIds,
                        'supplier'  => $supplierXmlIds
                    ]
                ],
                'order'  => 'desc'
            ];
        }

        return $this->availabilitySortScript;
    }

    /**
     * Сортирует по названию цвета, затем по полю SORT размера
     *
     * @param array|null $unionOffersSort
     */
    public function colorWithSizeSort(?array &$unionOffersSort = []): void
    {
        usort($unionOffersSort, static function($a, $b) {
            /** @var Offer $a */
            /** @var Offer $b */
            $aClothingSize = $a->getClothingSize();
            $bClothingSize = $b->getClothingSize();
            if ($aClothingSize && $bClothingSize) {
                $clothingSizeComparisonResult = $aClothingSize->getSort() <=> $bClothingSize->getSort();
            }

            $aColor = $a->getColor();
            $bColor = $b->getColor();
            if ($aColor && $bColor) {
                $colorComparisonResult = $aColor->getName() <=> $bColor->getName();
            }

            if (isset($colorComparisonResult)) {
                if ($colorComparisonResult === 0 && isset($clothingSizeComparisonResult)) {
                    return $clothingSizeComparisonResult;
                } else {
                    return $colorComparisonResult;
                }
            } elseif (isset($clothingSizeComparisonResult)) {
                return $clothingSizeComparisonResult;
            }

            return 0;
        });
    }
}
