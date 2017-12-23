<?php

namespace FourPaws\CatalogBundle\Service;

use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\Location\LocationService;

class SortService
{
    public function getSorts(string $activeSortCode, bool $isQuery = false)
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

    protected function getBaseSorts(): array
    {
        //$currentRegionCode = $this->locationService->getCurrentRegionCode();
        $currentRegionCode = LocationService::DEFAULT_REGION_CODE;

        return [
            (new Sorting())->withValue('popular')
                ->withName('популярности')
                ->withRule(['SORT' => ['order' => 'asc']]),

            (new Sorting())->withValue('up-price')
                ->withName('возрастанию цены')
                ->withRule(
                    [
                        'offers.prices.PRICE' => [
                            'order'         => 'asc',
                            'mode'          => 'min',
                            'nested_path'   => 'offers.prices',
                            'nested_filter' => [
                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                            ],

                        ],
                    ]
                ),

            (new Sorting())->withValue('down-price')
                ->withName('убыванию цены')
                ->withRule(
                    [
                        'offers.prices.PRICE' => [
                            'order'         => 'desc',
                            'mode'          => 'max',
                            'nested_path'   => 'offers.prices',
                            'nested_filter' => [
                                'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                            ],

                        ],
                    ]
                ),
        ];
    }
}
