<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductController
 *
 * @package FourPaws\CatalogBundle\Controller
 *
 * @Route("/catalog")
 */
class ProductController extends Controller
{
    /**
     * @var CatalogLandingService
     */
    private $landingService;

    /**
     * ProductController constructor.
     *
     * @param CatalogLandingService $landingService
     */
    public function __construct(CatalogLandingService $landingService)
    {
        $this->landingService = $landingService;
    }

    /**
     * @Route("/{path}/{slug}.html", requirements={"path"="[^\.]+(?!\.html)"})
     *
     * @param ProductDetailRequest $productDetailRequest
     * @param Request $request
     *
     * @return Response
     */
    public function productDetailAction(ProductDetailRequest $productDetailRequest, Request $request): Response
    {
        return $this->render('FourPawsCatalogBundle:Catalog:productDetail.html.php', [
            'productDetailRequest' => $productDetailRequest,
            'landingService' => $this->landingService,
            'request' => $request,

        ]);
    }
}
