<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\BannersRequest;
use FourPaws\MobileApiBundle\Dto\Response\BannerListResponse;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Services\Api\BannerService as ApiBannerService;
use FourPaws\Helpers\TaggedCacheHelper;
use Bitrix\Main\Application;

class FeedbackController extends FOSRestController
{
    /**
     * @Rest\Post("/feedback/")
     * @Rest\View()
     *
     * @return FeedbackResponse
     */
    public function postFeedbackAction(): FeedbackResponse
    {
        //toDo
        return (new FeedbackResponse('Ваше обращение принято'));
    }

    /**
     * @Rest\Post("/report/")
     * @Rest\View()
     *
     * @return FeedbackResponse
     */
    public function postReportAction(): FeedbackResponse
    {
        //toDo
        return (new FeedbackResponse('report_send_ok'));
    }
}
