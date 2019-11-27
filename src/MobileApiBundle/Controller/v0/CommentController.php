<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Exception\NotFoundProductException;
use Symfony\Component\HttpFoundation\Request;



class CommentController extends BaseController
{
    /**
     * @Rest\Get("/all_comments/")
     * @Rest\View(serializerGroups={"Default"})
     * @param Request $request
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function allComments(Request $request)
    {
        echo '<pre>';
        print_r($request);
        echo '</pre>';
        die;
        $offer = $this->apiProductService->getOne($goodsItemRequest->getId());
        return (new Response())->setData([
            'goods' => $offer
        ]);
    }
    
    /**
     * @Rest\Post("/add_comment/")
     * @Rest\View(serializerGroups={"Default", "product"})
     * @param Request $request
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function addComment(Request $request)
    {
        echo '<pre>';
        print_r($request);
        echo '</pre>';
        die;
        $offer = $this->apiProductService->getOne($goodsItemRequest->getId());
        return (new Response())->setData([
            'goods' => $offer
        ]);
    }
}
