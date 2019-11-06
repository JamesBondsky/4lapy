<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use FourPaws\MobileApiBundle\Dto\Request\QuestBarcodeRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestPrizeRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestQuestionRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestStartRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\QuestBarcodeTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestStartResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestPrizeResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestQuestionTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterPostResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException as ApiRuntimeException;
use FourPaws\MobileApiBundle\Services\Api\QuestService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class QuestController
 * @package FourPaws\MobileApiBundle\Controller
 */
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
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getRegisterAction(Request $request): Response
    {
        return new Response(['register' => $this->apiQuestService->getQuestRegisterStatus()]);
    }

    /**
     * @Rest\Post(path="/quest_register/")
     * @Rest\View()
     *
     * @param QuestRegisterRequest $questRegisterRequest
     *
     * @return Response|QuestRegisterPostResponse
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function postRegisterAction(QuestRegisterRequest $questRegisterRequest): QuestRegisterPostResponse
    {
        $this->apiQuestService->registerUser($questRegisterRequest);

        try {
            return (new QuestRegisterPostResponse())->setPetTypes($this->apiQuestService->getPetTypes());
        } catch (Exception $e) {
            throw new ApiRuntimeException('Произошла ошибка');
        }
    }

    /**
     * @Rest\Post(path="/quest_start/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestStartRequest $questStartRequest
     * @return QuestStartResponse
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function postStartAction(QuestStartRequest $questStartRequest): QuestStartResponse
    {
        $this->apiQuestService->startQuest($questStartRequest);

        return (new QuestStartResponse())->setBarcodeTask($this->apiQuestService->getCurrentBarcodeTask());
    }

    /**
     * @Rest\Post(path="/quest_barcode/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestBarcodeRequest $questBarcodeRequest
     * @return Response
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function postBarcodeAction(QuestBarcodeRequest $questBarcodeRequest): Response
    {
        $currentTask = $this->apiQuestService->getCurrentTask();

        $response = (new QuestBarcodeTaskResponse())
            ->setResult($this->apiQuestService->checkBarcodeTask($questBarcodeRequest))
            ->setCorrectText($currentTask['UF_CORRECT_TEXT'])
            ->setErrorText($currentTask['UF_TASK']);


        if ($response->getResult() === BarcodeTask::SUCCESS_SCAN) {
            $response->setQuestionTask($this->apiQuestService->getCurrentQuestionTask());
        }

        $response->setQuestStatus($this->apiQuestService->getQuestStatus());

        return new Response(['task_result' => $response]);
    }

    /**
     * @Rest\Post(path="/quest_question/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestQuestionRequest $questQuestionRequest
     * @return Response
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function postQuestionAction(QuestQuestionRequest $questQuestionRequest): Response
    {
        $currentTask = $this->apiQuestService->getCurrentTask();
        $questStatus = $this->apiQuestService->getQuestStatus();
        $response = new QuestQuestionTaskResponse();

        if ($questStatus->getNumber() === $questStatus->getTotalCount()) {
            $userResult = $this->apiQuestService->getUserResult();
            /** @var Pet $pet */
            $userPet = current($this->apiQuestService->getPetTypes([$userResult['UF_PET']]));
            $response->setPrizes($userPet->getPrizes());
        } else {
            $response->setBarcodeTask($this->apiQuestService->getCurrentBarcodeTask());
        }

        $response
            ->setCorrect($this->apiQuestService->checkQuestionTask($questQuestionRequest))
            ->setErrorText($currentTask['UF_QUESTION_ERROR'])
            ->setQuestStatus($this->apiQuestService->getQuestStatus());

        return new Response(['task_result' => $response]);
    }

    /**
     * @Rest\Post(path="/quest_prize/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param QuestPrizeRequest $questPrizeRequest
     * @return QuestPrizeResponse
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function postPrizeAction(QuestPrizeRequest $questPrizeRequest): QuestPrizeResponse
    {
        $this->apiQuestService->choosePrize($questPrizeRequest->getPrizeId());

        return (new QuestPrizeResponse())
            ->setPromocode($this->apiQuestService->getUserPromocode());
    }
}
