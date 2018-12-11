<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\InheritedProperty\SectionValues;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\CombinedSearchResult;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use FourPaws\Search\Table\SearchRequestStatisticTable;
use InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class SearchController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/search")
 */
class SearchController extends Controller
{

    /**
     * @Route("/autocomplete/")
     *
     * @param SearchRequest $searchRequest
     *
     * @return JsonResponse
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function autocompleteAction(SearchRequest $searchRequest): JsonResponse
    {
        $res = [];

        /** @var SearchService $searchService */
        $searchService = Application::getInstance()->getContainer()->get('search.service');
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');

        if (!$validator->validate($searchRequest)->count()) {

            //костыль для заказчика
            $searchString = mb_strtolower($searchRequest->getSearchString());
            if (preg_match('/[А-Яа-яЁё]/u', $searchString)) {
                $arSelect = [
                    'ID',
                    'IBLOCK_ID',
                    'NAME',
                    'PROPERTY_TRANSLITS'
                ];

                $arFilter = [
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS),
                    'ACTIVE' => 'Y',
                    '!PROPERTY_TRANSLITS' => false
                ];

                $dbItems = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
                while ($arItem = $dbItems->Fetch()) {
                    if (!empty($arItem['PROPERTY_TRANSLITS_VALUE'])) {
                        $arTranslits = explode(',', $arItem['PROPERTY_TRANSLITS_VALUE']);
                        foreach ($arTranslits as $translit) {
                            $translit = mb_strtolower(trim($translit));
                            if (mb_strpos($searchString, $translit) !== false) {
                                $searchString = str_replace($translit,
                                    mb_strtolower($arItem['NAME']), $searchString);
                            }
                        }
                    }

                }
            }

            /** @var CombinedSearchResult $result */
            $result = $searchService->searchAll(
                $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchString
            );

            $res = [
                'brands' => [],
                'products' => [],
                'suggests' => [],
            ];

            $converted = $result->getCollection()->toArray();

            /** @var Product|Brand $product */
            foreach ($converted as $key => $arItems) {
                foreach ($arItems as $item) {
                    if ($item instanceof Brand) {
                        $res['brands'][] = [
                            'DETAIL_PAGE_URL' => $item->getDetailPageUrl(),
                            'NAME' => $item->getName(),
                            'SCORE' => $item->getHitMetaInfo()->getScore(),
                        ];
                    } elseif ($item instanceof Product) {
                        /**
                         * @var Offer $offer
                         */
                        $offer = $item->getOffers()->first();

                        if ($key == 'products') {
                            /**
                             * @var Image $image
                             */
                            $images = $offer->getImages();
                            if ($images != false && $images != null) {
                                $image = $offer->getImages()->first();
                                $res[$key][] = [
                                    'DETAIL_PAGE_URL' => $item->getDetailPageUrl(),
                                    'NAME' => $item->getName(),
                                    'PREVIEW' => sprintf("/upload/%s/%s", $image->getSubDir(), $image->getFileName()),
                                    'PRICE' => $offer->getPrice(),
                                    'CURRENCY' => $offer->getCurrency(),
                                    'BRAND' => $item->getBrand()->getName(),
                                    'SCORE' => $item->getHitMetaInfo()->getScore()
                                ];
                            } else {
                                $res[$key][] = [
                                    'DETAIL_PAGE_URL' => $item->getDetailPageUrl(),
                                    'NAME' => $item->getName(),
                                    'PRICE' => $offer->getPrice(),
                                    'CURRENCY' => $offer->getCurrency(),
                                    'BRAND' => $item->getBrand()->getName(),
                                    'SCORE' => $item->getHitMetaInfo()->getScore()
                                ];
                            }
                        } elseif ($key == 'suggests') {
                            if ($offer != false) {
                                if (empty($res[$key][$offer->getProduct()->getIblockSectionId()])) {
//                                    $curScore = $item->getHitMetaInfo()->getScore();

                                    $category = $offer->getProduct()->getSection();
                                    if ($category) {
                                        $cache = (new BitrixCache())
                                            ->withId(__METHOD__ . $category->getId())
                                            ->withTime(3600);

                                        $sectionProps = $cache->resultOf(function () use ($category) {
                                            return \array_map(function ($meta) use ($category) {
                                                return $meta;
                                            }, (new SectionValues($category->getIblockId(),
                                                $category->getId()))->getValues());
                                        });

                                        $res[$key][$offer->getProduct()->getIblockSectionId()] = [
                                            'NAME' => $sectionProps['SECTION_PAGE_TITLE'],
                                            'DETAIL_PAGE_URL' => $offer->getProduct()->getSection()->getSectionPageUrl() .
                                                '?query=' . str_replace(' ', '+', $searchRequest->getSearchString()),
                                            'SCORE' => $item->getHitMetaInfo()->getScore(),
//                                        'ELEMENTS' => [
//                                            [
//                                                'NAME' => $offer->getProduct()->getName(),
//                                                'URL' => $offer->getProduct()->getDetailPageUrl(),
//                                                'SCORE' => $curScore
//                                            ]
//                                        ]
                                        ];
                                    }
                                } else {
                                    $curScore = $item->getHitMetaInfo()->getScore();
                                    $res[$key][$offer->getProduct()->getIblockSectionId()]['SCORE'] += $curScore;
//                                    $res[$key][$offer->getProduct()->getIblockSectionId()]['ELEMENTS'][] = [
//                                        'NAME' => $offer->getProduct()->getName(),
//                                        'URL' => $offer->getProduct()->getDetailPageUrl(),
//                                        'SCORE' => $curScore
//                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        usort($res['suggests'], function ($a, $b) {
            if ($a['SCORE'] == $b['SCORE']) {
                return 0;
            }
            return ($a['SCORE'] < $b['SCORE']) ? 1 : -1;
        });

        if (count($res['products']) >= 5) {
            $res['products'] = [];
        } elseif (isset($res['products'][0])) {
            $res['products'] = [$res['products'][0]];
            $res['suggests'] = [];
            $res['brands'] = [];
        } else {
            $res['products'] = [];
        }

        return JsonSuccessResponse::createWithData('', $res)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * @Route("/write_statistic/")
     *
     * @param SearchRequest $searchRequest
     *
     * @return JsonResponse
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function writeStatisticAction(SearchRequest $searchRequest): JsonResponse
    {
        $statisticDb = SearchRequestStatisticTable::GetList([
            'filter' => [
                'search_string' => $searchRequest->getSearchString()
            ]
        ]);

        if ($statisticDb->getSelectedRowsCount() == 0) {
            SearchRequestStatisticTable::Add([
                'search_string' => $searchRequest->getSearchString(),
                'quantity' => 1,
                'last_date_search' => new DateTime()
            ]);
        } else {
            $statisticRow = $statisticDb->fetch();
            SearchRequestStatisticTable::Update(
                $statisticRow['id'],
                [
                    'quantity' => $statisticRow['quantity'] + 1,
                    'last_date_search' => new DateTime()
                ]
            );
        }
        return JsonSuccessResponse::createWithData('', []);
    }
}
