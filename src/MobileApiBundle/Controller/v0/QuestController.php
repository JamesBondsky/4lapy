<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Services\Api\QuestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function getRegisterAction(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Rest\Post(path="/quest_register/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestRegisterRequest $questRegisterRequest
     * @return Response
     */
    public function postRegisterAction(QuestRegisterRequest $questRegisterRequest): Response
    {
        return (new Response())->setData($this->apiQuestService->registerUser($questRegisterRequest));
    }

    /**
     * @Rest\Post(path="/quest_pet/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
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
     * @Security("has_role('REGISTERED_USERS')")
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
     * @Security("has_role('REGISTERED_USERS')")
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
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param Request $request
     * @return Response
     */
    public function postPrizeAction(Request $request): Response
    {
        return new Response();
    }
}
