<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class BrandController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/{brand}/")
     * @param \FourPaws\CatalogBundle\Dto\CatalogBrandRequest $catalogBrandRequest
     */
    public function detailAction(CatalogBrandRequest $catalogBrandRequest)
    {
        dump($catalogBrandRequest);
        die();
    }
}
