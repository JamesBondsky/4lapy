<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CatalogController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/catalog2")
 */
class CatalogController extends Controller
{
    /**
     * @var \CAllMain|\CMain
     */
    private $bitrixApp;

    public function __construct()
    {
        global $APPLICATION;
        $this->bitrixApp = $APPLICATION;
    }

    /**
     * @Route("/")
     */
    public function rootAction()
    {
        return $this->redirect('/');
    }

    /**
     * @Route("/{rootCategoryRequest}/")
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
     * @ParamConverter(name="categoryRequest", options={"path"="path"})
     * @param ChildCategoryRequest $categoryRequest
     *
     * @return Response
     */
    public function childCategoryAction(ChildCategoryRequest $categoryRequest)
    {
        dump($categoryRequest);
        die();

        return new Response('');
    }
}
