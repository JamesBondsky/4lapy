<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\Catalog\Model\Category;
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
     */
    public function rootCategoryAction(Category $category)
    {
        return new Response('');
    }

    /**
     * @Route("/{path}/", requirements={"path"="[^\.]+(?!\.html)$" })
     */
    public function childCategoryAction(string $path)
    {
        return new Response($path);
    }

    /**
     * @Route("/{path}/{slug}.html", requirements={"path"="[^\.]+(?!\.html)"})
     */
    public function productAction(string $path, string $slug)
    {
        return new Response($path . ' + ' . $slug);
    }
}
