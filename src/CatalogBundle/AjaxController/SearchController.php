<?php

namespace FourPaws\CatalogBundle\AjaxController;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/search")
 */
class SearchController
{
    const SEARCH_QUERY_MIN_LENGTH = 3;

    /**
     * @Route("/autocomplete/")
     *
     * @param SearchRequest $searchRequest
     *
     * @return JsonResponse
     */
    public function autocompleteAction(Request $request, SearchRequest $searchRequest): JsonResponse
    {
        $res = [];

        if (mb_strlen($request->query->get('text')) >= self::SEARCH_QUERY_MIN_LENGTH) {
            /** @var SearchService $searchService */
            $searchService = Application::getInstance()->getContainer()->get('search.service');
            /** @var ProductSearchResult $result */
            $result = $searchService->searchProducts(
                new FilterCollection(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchRequest->getSearchString()
            );

            /** @var Product $product */
            foreach ($result->getProductCollection() as $product) {
                $res[] = ['DETAIL_PAGE_URL' => $product->getDetailPageUrl(), 'NAME' => $product->getName()];
            }
        }

        return JsonSuccessResponse::createWithData('', $res);
    }
}
