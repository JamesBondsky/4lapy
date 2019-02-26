<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\Api\LaunchService as ApiLaunchService;

class LaunchController extends FOSRestController
{
    /** @var ApiLaunchService */
    private $apiLaunchService;

    public function __construct(
        ApiLaunchService $apiLaunchService
    )
    {
        $this->apiLaunchService = $apiLaunchService;
    }

    /**
     * @Rest\Get("/app_launch/")
     * @Rest\View()
     *
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function launchAction()
    {
        $this->apiLaunchService->onLaunchApp();
        return (new Response())
            ->setData([]);
    }
}
