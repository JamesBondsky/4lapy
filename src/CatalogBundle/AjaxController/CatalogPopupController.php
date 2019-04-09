<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 08.04.2019
 * Time: 18:30
 */

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PopupController
 *
 * @package FourPaws\CatalogBundle\AjaxController
 * @Route("/catalogPopup")
 */
class CatalogPopupController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @Route("/getCatalog/", methods={"GET"})
     */
    public function getCatalog()
    {
        $rootCategoryRequest = new RootCategoryRequest();
        $rootCategoryRequest->setCategorySlug('/');
        $rootCategoryRequest->setCategory(Category::createRoot());

        include __DIR__ . '/../Resources/views/Catalog/rootCategory.html.php';
    }
}