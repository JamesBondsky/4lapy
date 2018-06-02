<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OldProductController
 *
 * @package FourPaws\CatalogBundle\Controller
 *
 * @Route("/online_shop")
 */
class OldProductController extends Controller
{
    /**
     * @Route("/{path}", requirements={"path"=".+"})
     * @param string $path
     *
     * @return Response
     */
    public function oldProductDetailAction(string $path): Response
    {
        if (empty($path)) {
            return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
        }
        /** если элемент не активен то 404 - отключено - ибо - в стандартном запрсое оффера такого фильтра нет*/
        $newUrl = '';
        $fullPath = '/online_shop/' . $path;
        $fullPathWithEndSlash = $fullPath . '/';
        $offerCollection = (new OfferQuery())->withFilter(['=PROPERTY_OLD_URL' => [$fullPath, $fullPathWithEndSlash]])->exec();
        if (!$offerCollection->isEmpty()) {
            /** @var Offer $offer */
            $offer = $offerCollection->first();
            $newUrl = $offer->getLink();
        }
        if (!empty($newUrl)) {
            LocalRedirect($newUrl, true, '301 Moved Permanently');
            die();
        }

        return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
    }
}
