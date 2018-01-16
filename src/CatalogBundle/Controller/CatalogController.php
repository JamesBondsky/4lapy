<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\App\Application;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\Search\Model\ProductSearchResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CatalogController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/catalog")
 */
class CatalogController extends Controller
{
    /**
     * @Route("/")
     */
    public function rootAction()
    {
        return $this->redirect('/');
    }

    /**
     * @Route("/search/")
     */
    public function searchAction(Request $request, SearchRequest $searchRequest): Response
    {
        $result = null;
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        if (!$validator->validate($searchRequest)->count()) {
            /** @var ProductSearchResult $result */
            $result = Application::getInstance()->getContainer()->get('search.service')->searchProducts(
                new FilterCollection(),
    //            $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchRequest->getSearchString()
            );
        }

        $categories = (new CategoryQuery())
            ->withFilterParameter('SECTION_ID', false)
            ->exec();

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:search.filter.container.html.php';
        } else {
            $tpl = 'FourPawsCatalogBundle:Catalog:search.html.php';
        }

        return $this->render($tpl, [
            'request'             => $request,
            'productSearchResult' => $result,
            'catalogRequest'      => $searchRequest,
            'categories'      => $categories
        ]);
    }

    /**
     * @Route("/{path}/")
     *
     * @param RootCategoryRequest $rootCategoryRequest
     *
     * @return Response
     */
    public function rootCategoryAction(RootCategoryRequest $rootCategoryRequest)
    {
        return $this->render('FourPawsCatalogBundle:Catalog:rootCategory.html.php', [
            'rootCategoryRequest' => $rootCategoryRequest,
        ]);
    }

    /**
     * @Route("/{path}/", requirements={"path"="[^\.]+(?!\.html)$" })
     * @param Request              $request
     * @param ChildCategoryRequest $categoryRequest
     *
     * @throws \FourPaws\CatalogBundle\Exception\RuntimeException
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return Response
     */
    public function childCategoryAction(Request $request, ChildCategoryRequest $categoryRequest): Response
    {
        $result = Application::getInstance()->getContainer()->get('search.service')->searchProducts(
            $categoryRequest->getCategory()->getFilters(),
            $categoryRequest->getSorts()->getSelected(),
            $categoryRequest->getNavigation(),
            $categoryRequest->getSearchString()
        );

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php';
        } else {
            $tpl = 'FourPawsCatalogBundle:Catalog:catalog.html.php';
        }

        return $this->render($tpl, [
            'request'             => $request,
            'productSearchResult' => $result,
            'catalogRequest'      => $categoryRequest,
        ]);
    }
}
