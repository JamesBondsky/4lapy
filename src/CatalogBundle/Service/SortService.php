<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Service;

use Bitrix\Main\ArgumentException;
use Elastica\QueryBuilder;
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

    protected function getRelevantSort()
    {
        return (new Sorting())
            ->withValue('relevance')
            ->withName('релевантности')
            ->withRule(['_score']);
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
            (new Sorting())->withValue('popular')
                ->withName('популярности')
                ->withRule([
                    '_script' => $this->getAvaliabilitySort(),
                    'SORT' => ['order' => 'asc'],
                    '_score' => ['order' => 'desc'],
                    'ID' => ['order' => 'desc']
                ]),

            (new Sorting())->withValue('up-price')
                ->withName('возрастанию цены')
                ->withRule(
                    [
                        'offers.price' => [
                            'order'       => 'asc',
                            'mode'        => 'max',//ибо по умолчанию выбирается максимальная фасовка
                            'nested_path' => 'offers',
                        ],
                        //                        'offers.prices.PRICE' => [
                        //                            'order'         => 'asc',
                        //                            'mode'          => 'min',
                        //                            'nested_path'   => 'offers.prices',
                        //                            'nested_filter' => [
                        //                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                        //                            ],
                        //
                        //                        ],
                        '_score' => ['order' => 'desc']
                    ]
                ),

            (new Sorting())->withValue('down-price')
                ->withName('убыванию цены')
                ->withRule(
                    [
                        'offers.price' => [
                            'order'       => 'desc',
                            'mode'        => 'max',
                            'nested_path' => 'offers',
                        ],
                        //                        'offers.prices.PRICE' => [
                        //                            'order'         => 'desc',
                        //                            'mode'          => 'max',
                        //                            'nested_path'   => 'offers.prices',
                        //                            'nested_filter' => [
                        //                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                        //                            ],
                        //
                        //                        ],
                        '_score' => ['order' => 'desc']
                    ]
                ),
        ];
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    protected function getAvaliabilitySort(): array
    {
        $deliveries = $this->deliveryService->getByLocation();

        $availableXmlIds = [];
        foreach ($deliveries as $delivery) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $availableXmlIds = \array_merge($availableXmlIds, $this->deliveryService->getStoresByDelivery($delivery)->getXmlIds());
        }
        $availableXmlIds = \array_unique($availableXmlIds);

        $supplierXmlIds = $this->storeService->getSupplierStores()->getXmlIds();

        return [
            'type' => 'number',
            'script' => [
                'lang' => 'painless',
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
                    'supplier' => $supplierXmlIds
                ]
            ],
            'order' => 'desc'
        ];
    }
}
