<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Services\Api\QuestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     *
     * @param Request $request
     * @return Response
     */
    public function getRegisterAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_register/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function postRegisterAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_pet/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function postPetAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_barcode/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function postBarcodeAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_question/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function postQuestionAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_prize/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function postPrizeAction(Request $request): Response
    {
        return new Response();
    }
}
