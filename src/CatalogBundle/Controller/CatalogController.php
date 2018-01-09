<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\App\Application;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
