<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\FeedbackRequest;
use FourPaws\MobileApiBundle\Dto\Request\ReportRequest;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Services\Api\FeedbackService as ApiFeedbackService;

class FeedbackController extends FOSRestController
{
    /** @var ApiFeedbackService */
    private $apiFeedbackService;

    public function __construct(
        ApiFeedbackService $apiFeedbackService
    )
    {
        $this->apiFeedbackService = $apiFeedbackService;
    }

    /**
     * @Rest\Post("/feedback/")
     * @Rest\View()
     *
     * @param FeedbackRequest $feedbackRequest
     * @return FeedbackResponse
     */
    public function postFeedbackAction(FeedbackRequest $feedbackRequest): FeedbackResponse
    {
        $this->apiFeedbackService->sendFeedback($feedbackRequest);
        return (new FeedbackResponse('Ваше обращение принято'));
    }

    /**
     * @Rest\Post("/report/")
     * @Rest\View()
     *
     * @param ReportRequest $reportRequest
     * @return FeedbackResponse
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     */
    public function postReportAction(ReportRequest $reportRequest): FeedbackResponse
    {
        $this->apiFeedbackService->sendReport($reportRequest);
        return (new FeedbackResponse('Ваше обращение принято'));
    }
}
