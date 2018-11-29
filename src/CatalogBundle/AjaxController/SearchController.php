<?php

namespace FourPaws\CatalogBundle\AjaxController;

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
use FourPaws\Search\Model\CombinedSearchResult;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            /** @var CombinedSearchResult $result */
            $result = $searchService->searchAll(
                $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchRequest->getSearchString()
            );

            $res = [
                'brands' => [],
                'products' => [],
                'suggests' => [],
            ];

            $converted = $result->getCollection()->toArray();
//            $converted['suggests'] = $searchService->productsAutocomplete(
//                $searchRequest->getNavigation(),
//                $searchRequest->getSearchString())
//                ->getProductCollection()
//                ->toArray();
            // отрубил такой вывод пока

            /** @var Product|Brand $product */
            foreach ($converted as $key => $arItems) {
                foreach ($arItems as $item) {
                    if ($item instanceof Brand) {
                        $res['brands'][] = ['DETAIL_PAGE_URL' => $item->getDetailPageUrl(), 'NAME' => $item->getName()];
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
                                    'BRAND' => $item->getBrand()->getName()
                                ];
                            } else {
                                $res[$key][] = [
                                    'DETAIL_PAGE_URL' => $item->getDetailPageUrl(),
                                    'NAME' => $item->getName(),
                                    'PRICE' => $offer->getPrice(),
                                    'CURRENCY' => $offer->getCurrency(),
                                    'BRAND' => $item->getBrand()->getName()
                                ];
                            }
                        } elseif ($key == 'suggests') {
                            if ($offer != false) {
                                if (empty($res[$key][$offer->getProduct()->getIblockSectionId()])) {
                                    $res[$key][$offer->getProduct()->getIblockSectionId()] = [
                                        'NAME' => $offer->getProduct()->getSection()->getName(),
                                        'DETAIL_PAGE_URL' => $offer->getProduct()->getSection()->getSectionPageUrl(),
                                        'ELEMENTS' => [
                                            [
                                                'NAME' => $offer->getProduct()->getBrandName() . ' ' . $offer->getName(),
                                                'LINK' => $offer->getProduct()->getDetailPageUrl()
                                            ]
                                        ]
                                    ];
                                } else {
                                    $res[$key][$offer->getProduct()->getIblockSectionId()]['ELEMENTS'][] = [
                                        'NAME' => $offer->getProduct()->getBrandName() . ' ' . $offer->getName(),
                                        'LINK' => $offer->getProduct()->getDetailPageUrl()
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return JsonSuccessResponse::createWithData('', $res)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
