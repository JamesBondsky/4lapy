<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\CatalogBundle\Dto\CategoryRequest;
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
     * @Route("/{category}/")
     * @ParamConverter(name="categoryRequest", options={"path"="category"})
     * @param CategoryRequest $categoryRequest
     *
     * @return Response
     */
    public function rootCategoryAction(CategoryRequest $categoryRequest)
    {
        dump($categoryRequest);
        die();
        return new Response('');
    }

    /**
     * @Route("/{path}/", requirements={"path"="[^\.]+(?!\.html)$" })
     * @ParamConverter(name="categoryRequest", options={"path"="path"})
     * @param CategoryRequest $categoryRequest
     *
     * @return Response
     */
    public function childCategoryAction(CategoryRequest $categoryRequest)
    {
        dump($categoryRequest);
        die();

        return new Response('');
    }

    /**
     * @Route("/{path}/{slug}.html", requirements={"path"="[^\.]+(?!\.html)"})
     */
    public function productAction(string $path, string $slug)
    {
        return new Response($path . ' + ' . $slug);
    }
}
