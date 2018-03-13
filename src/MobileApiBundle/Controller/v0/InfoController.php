<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\Api\InfoService;

class InfoController extends FOSRestController
{
    /**
     * @var InfoService
     */
    private $infoService;

    public function __construct(InfoService $infoService)
    {
        $this->infoService = $infoService;
    }

    /**
     * Получить статичные разделы
     *
     * @todo Статичные страницы, Вакансии, Конкурсы, Условия доставки
     * @Rest\Get("/info/")
     * @Rest\View()
     *
     * @param InfoRequest $infoRequest
     *
     * @return Response
     */
    public function getInfoAction(InfoRequest $infoRequest): Response
    {
        $response = new Response();
        $response->setData($this->infoService->getInfo(
            $infoRequest->getType(),
            $infoRequest->getInfoId(),
            $infoRequest->getFields()
        ));

        return $response;
    }
}
