<?php

namespace FourPaws\CatalogBundle\AjaxController;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\SearchRequest;
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
            /** @var ProductSearchResult $result */
            $result = $searchService->searchProducts(
                $searchRequest->getCategory()->getFilters(),
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
