<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 08.04.2019
 * Time: 18:30
 */

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Controller\CatalogController;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PopupController
 *
 * @package FourPaws\CatalogBundle\AjaxController
 * @Route("/popup")
 */
class CatalogPopupController extends CatalogController implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private $landingService;

    /**
     * @Route("/", methods={"POST"})
     */
    public function getMain()
    {
        $rootCategoryRequest = new RootCategoryRequest();
        $rootCategoryRequest->setCategorySlug('/');
        $rootCategoryRequest->setCategory(Category::createRoot());

        include __DIR__ . '/../Resources/views/Catalog/rootCategory.html.php';
    }


    /**
     * Copy-past кода из ProductController
     *
     * @Route("/{path}/{slug}.html", requirements={"path"="[^\.]+(?!\.html)"})
     *
     * @param ProductDetailRequest $productDetailRequest
     * @param Request $request
     *
     * @return Response
     */
    public function productDetailAction(ProductDetailRequest $productDetailRequest, Request $request): Response
    {
        $landingService = Application::getInstance()->getContainer()->get(CatalogLandingService::class);
        return $this->render('FourPawsCatalogBundle:Catalog:productDetail.html.php', [
            'productDetailRequest' => $productDetailRequest,
            'landingService' => $landingService,
            'request' => $request,

        ]);
    }


}