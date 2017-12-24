<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/catalog")
 */
class ProductController extends Controller
{
    /**
     * @Route("/{path}/{slug}.html", requirements={"path"="[^\.]+(?!\.html)"})
     * @param ProductDetailRequest $productDetailRequest
     *
     * @return Response
     */
    public function productDetailRequest(ProductDetailRequest $productDetailRequest)
    {
        return $this->render('FourPawsCatalogBundle:Catalog:productDetail.html.php', [
            'productDetailRequest' => $productDetailRequest,
        ]);
    }
}
