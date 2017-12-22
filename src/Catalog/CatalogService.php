<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Elastica\Query\Term;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Location\LocationService;
use FourPaws\Search\Model\Navigation;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class CatalogService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * CatalogService constructor.
     *
     * @param DataManager $filterTable
     * @param Serializer $serializer
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Возвращает все доступные сортировки и отмечает активную
     *
     * @param Request $request
     *
     * @return Collection|Sorting[]
     */
    public function getSortings(Request $request): Collection
    {
        $currentRegionCode = $this->locationService->getCurrentRegionCode();

        $sortings = [

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

        //Если задана строка поиска
        if ($this->getSearchString($request) != '') {
            //Добавить сортировку по релевантности
            array_unshift(
                $sortings,
                (new Sorting())->withValue('relevance')
                               ->withName('релевантности')
                               ->withRule(['_score'])
            );
        }

        $sortingCollection = new ArrayCollection($sortings);

        $selectedSortValue = trim($request->query->get('sort'));

        //Определить выбранную сортировку
        $activeSorting = $sortingCollection->filter(
            function (Sorting $sorting) use ($selectedSortValue) {
                return $sorting->getValue() === $selectedSortValue;
            }
        )->current();

        //Если ничего не выбрано, то по умолчанию выбрать первую
        if (!($activeSorting instanceof Sorting)) {
            $activeSorting = $sortingCollection->first();
        }

        $activeSorting->withSelected(true);

        return $sortingCollection;
    }

    /**
     * Возвращает выбранную сортировку
     *
     * @param Request $request
     *
     * @throws RuntimeException
     * @return Sorting
     */
    public function getSelectedSorting(Request $request): Sorting
    {
        $selectedSorting = $this->getSortings($request)->filter(
            function (Sorting $sorting) {
                return $sorting->isSelected();
            }
        )->first();

        if (!($selectedSorting instanceof Sorting)) {
            throw new RuntimeException('Не удалось обнаружить выбранную сортировку');
        }

        return $selectedSorting;
    }

    /**
     * @param Request $request
     *
     * @return Navigation
     */
    public function getNavigation(Request $request): Navigation
    {
        $navRule = new Navigation();

        $pageNumber = (int)$request->query->get('page');

        if ($pageNumber > 0) {
            $navRule->withPage($pageNumber);
        }

        $pageSize = (int)$request->query->get('pageSize');

        /**
         * Здесь можно задать набор вариантов допустимых значений количества товаров на странице.
         */
        $availablePageSizes = [20, 40];
        if ($pageSize > 0 && in_array($pageSize, $availablePageSizes)) {
            $navRule->withPageSize($pageSize);
        } else {
            $navRule->withPage(reset($availablePageSizes));
        }

        return $navRule;
    }

    /**
     * Возвращает строку поиска, а если она не задана, то пустую строку.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getSearchString(Request $request): string
    {
        return trim($request->query->get('q'));
    }
}
