<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Dto\Response\QuestBarcodeTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestPrizeResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestQuestionTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterPostResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestStartResponse;
use FourPaws\MobileApiBundle\Services\Api\QuestService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class QuestController extends BaseController
{
    /**
     * @var QuestService
     */
    protected $apiQuestService;

    /**
     * @param QuestService $apiQuestService
     */
    public function __construct(QuestService $apiQuestService)
    {
        $this->apiQuestService = $apiQuestService;
    }

    /**
     * @Rest\Get(path="/quest_register/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return QuestRegisterGetResponse
     */
    public function getRegisterAction(Request $request): QuestRegisterGetResponse
    {
        return new QuestRegisterGetResponse();
    }

    /**
     * @Rest\Post(path="/quest_register/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestRegisterRequest $questRegisterRequest
     * @return QuestRegisterPostResponse
     */
    public function postRegisterAction(QuestRegisterRequest $questRegisterRequest): QuestRegisterPostResponse
    {
        return new QuestRegisterPostResponse();
    }

    /**
     * @Rest\Post(path="/quest_start/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return QuestStartResponse
     */
    public function postStartAction(Request $request): QuestStartResponse
    {
        return new QuestStartResponse();
    }

    /**
     * @Rest\Post(path="/quest_barcode/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return QuestBarcodeTaskResponse
     */
    public function postBarcodeAction(Request $request): QuestBarcodeTaskResponse
    {
        return new QuestBarcodeTaskResponse();
    }

    /**
     * @Rest\Post(path="/quest_question/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return QuestQuestionTaskResponse
     */
    public function postQuestionAction(Request $request): QuestQuestionTaskResponse
    {
        return new QuestQuestionTaskResponse();
    }

    /**
     * @Rest\Post(path="/quest_prize/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return QuestPrizeResponse
     */
    public function postPrizeAction(Request $request): QuestPrizeResponse
    {
        return new QuestPrizeResponse();
    }
}
