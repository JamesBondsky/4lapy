<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\App\Application;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @param ChildCategoryRequest $categoryRequest
     *
     * @throws \Exception
     * @return Response
     */
    public function childCategoryAction(ChildCategoryRequest $categoryRequest): Response
    {
        $result = Application::getInstance()->getContainer()->get('search.service')->searchProducts(
            $categoryRequest->getCategory()->getFilters(),
            $categoryRequest->getSorts()->getSelected(),
            $categoryRequest->getNavigation(),
            $categoryRequest->getSearchString()
        );

        return $this->render('FourPawsCatalogBundle:Catalog:catalog.html.php', [
            'productSearchResult' => $result,
            'catalogRequest'      => $categoryRequest,
        ]);
    }
}
