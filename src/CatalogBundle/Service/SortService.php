<?php

namespace FourPaws\CatalogBundle\Service;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
        $deliveries = $this->deliveryService->getByLocation();

        $availableXmlIds = [];
        foreach ($deliveries as $delivery) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $availableXmlIds = \array_merge($availableXmlIds, $this->deliveryService->getStoresByDelivery($delivery)
                                                                                    ->getXmlIds());
        }
        $availableXmlIds = \array_unique($availableXmlIds);

        $supplierXmlIds = $this->storeService->getSupplierStores()->getXmlIds();

        return [
            'type'   => 'number',
            'script' => [
                'lang'   => 'painless',
                'source' => '
                    for (int i = 0; i < doc[\'availableStores\'].length; ++i) {
                        for (int j = 0; j < params.available.length; ++j) {
                            if (doc[\'availableStores\'][i] == params.available[j]) {
                                return 2;
                            }
                        }
                    }

                    for (int i = 0; i < doc[\'availableStores\'].length; ++i) {
                        for (int j = 0; j < params.supplier.length; ++j) {
                            if (doc[\'availableStores\'][i] == params.supplier[j]) {
                                return 1;
                            }
                        }
                    }

                    return 0;
                ',
                'params' => [
                    'available' => $availableXmlIds,
                    'supplier'  => $supplierXmlIds
                ]
            ],
            'order'  => 'desc'
        ];
    }
}
