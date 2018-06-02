<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Controller;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OldPublicationController
 *
 * @package FourPaws\AppBundle\AjaxController
 */
class OldPublicationController extends Controller
{
    /**
     * @param string $path
     *
     * @return Response
     * @throws IblockNotFoundException
     */
    public function oldNewsDetailRequest(string $path): Response
    {
        if (empty($path)) {
            return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
        }
        /** если элемент не активен то 404 */
        $fullPath = '/actions_and_programs/news/' . $path;
        $fullPathWithEndSlash = $fullPath . '/';
        $newUrl = '';
        $propCode = 'OLD_URL';
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS);
        $res = \CIBlockElement::GetList(
            [],
            [
                '=PROPERTY_' . $propCode => [$fullPath, $fullPathWithEndSlash],
                'IBLOCK_ID'             => $iblockId,//нельзя ставить равно когда естьфильтр по свойствам - разрыв шаблона)
                '=ACTIVE'                => 'Y', //нужно, ибо стоит првоерка активности в компоненте
                'ACTIVE_DATE' => 'Y' //нужно, ибо стоит првоерка активности в компоненте
            ],
            false,
            ['nTopCount' => 1],
            ['DETAIL_PAGE_URL']
        );
        if ($res->SelectedRowsCount() > 0) {
            $bxItem = $res->GetNext();
            $newUrl = $bxItem['DETAIL_PAGE_URL'];
        }

        if (!empty($newUrl)) {
            LocalRedirect($newUrl, true, '301 Moved Permanently');
            die();
        }

        return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws IblockNotFoundException
     */
    public function oldArticleDetailRequest(string $path): Response
    {
        if (empty($path)) {
            return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
        }
        /** если элемент не активен то 404 */
        $fullPath = '/actions_and_programs/article/' . $path;
        $fullPathWithEndSlash = $fullPath . '/';
        $newUrl = '';
        $propCode = 'OLD_URL';
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES);
        $res = \CIBlockElement::GetList(
            [],
            [
                '=PROPERTY_' . $propCode => [$fullPath, $fullPathWithEndSlash],
                'IBLOCK_ID'             => $iblockId,//нельзя ставить равно когда естьфильтр по свойствам - разрыв шаблона)
                '=ACTIVE'                => 'Y',//нужно, ибо стоит првоерка активности в компоненте
                'ACTIVE_DATE' => 'Y'//нужно, ибо стоит првоерка активности в компоненте
            ],
            false,
            ['nTopCount' => 1],
            ['DETAIL_PAGE_URL']
        );
        if ($res->SelectedRowsCount() > 0) {
            $bxItem = $res->GetNext();
            $newUrl = $bxItem['DETAIL_PAGE_URL'];
        }

        if (!empty($newUrl)) {
            LocalRedirect($newUrl, true, '301 Moved Permanently');
            die();
        }

        return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws IblockNotFoundException
     */
    public function oldSaleDetailRequest(string $path): Response
    {
        if (empty($path)) {
            return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
        }
        /** если элемент не активен то 404 */
        $fullPath = '/actions_and_programs/' . $path;
        $fullPathWithEndSlash = $fullPath . '/';
        $newUrl = '';
        $propCode = 'OLD_URL';
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);
        $res = \CIBlockElement::GetList(
            [],
            [
                '=PROPERTY_' . $propCode => [$fullPath, $fullPathWithEndSlash],
                'IBLOCK_ID'             => $iblockId,//нельзя ставить равно когда естьфильтр по свойствам - разрыв шаблона)
                '=ACTIVE'                => 'Y',//нужно, ибо стоит првоерка активности в компоненте
                'ACTIVE_DATE' => 'Y'//нужно, ибо стоит првоерка активности в компоненте
            ],
            false,
            ['nTopCount' => 1],
            ['DETAIL_PAGE_URL']
        );
        if ($res->SelectedRowsCount() > 0) {
            $bxItem = $res->GetNext();
            $newUrl = $bxItem['DETAIL_PAGE_URL'];
        }

        if (!empty($newUrl)) {
            LocalRedirect($newUrl, true, '301 Moved Permanently');
            die();
        }

        return $this->render('FourPawsCatalogBundle:Catalog:oldItem.html.php');
    }
}