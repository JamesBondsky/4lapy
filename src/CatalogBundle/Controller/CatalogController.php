<?php

namespace FourPaws\CatalogBundle\Controller;

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
     * @Route("/{slug}/")
     */
    public function rootCategoryAction(string $slug)
    {
        return new Response($slug);
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
