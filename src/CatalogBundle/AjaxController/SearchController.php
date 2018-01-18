<?php

namespace FourPaws\CatalogBundle\AjaxController;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\Search\Model\ProductSuggestResult;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
     */
    public function autocompleteAction(Request $request, SearchRequest $searchRequest): JsonResponse
    {
        $res = [];

        /** @var SearchService $searchService */
        $searchService = Application::getInstance()->getContainer()->get('search.service');
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');

        if (!$validator->validate($searchRequest)->count()) {
            /** @var ProductSuggestResult $result */
            $result = $searchService->productsAutocomplete($searchRequest->getSearchString());

            /** @var Product $product */
            foreach ($result->getProductCollection() as $product) {
                $res[] = ['DETAIL_PAGE_URL' => $product->getDetailPageUrl(), 'NAME' => $product->getName()];
            }
        }

        return JsonSuccessResponse::createWithData('', $res);
    }
}
