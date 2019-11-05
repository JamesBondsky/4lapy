<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Object\Quest\AnswerVariant;
use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestionTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;
use FourPaws\MobileApiBundle\Dto\Request\QuestBarcodeRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestStartRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\QuestBarcodeTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestPrizeResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestQuestionTaskResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterPostResponse;
use FourPaws\MobileApiBundle\Dto\Response\QuestStartResponse;
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
     * @return Response
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function postStartAction(QuestStartRequest $questStartRequest): Response
    {
        $this->apiQuestService->startQuest($questStartRequest);

        return new Response(
            $this->apiQuestService->getCurrentBarcodeTask()
        );
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
        $task = $this->apiQuestService->getCurrentTask();

        $response = (new QuestBarcodeTaskResponse())
            ->setCorrectText($task['UF_CORRECT_TEXT'])
            ->setErrorText($task['UF_BARCODE_ERROR'])
            ->setResult($this->apiQuestService->checkBarcodeTask($questBarcodeRequest))
            ->setQuestStatus($this->apiQuestService->getQuestStatus());


        if ($response->getResult() === BarcodeTask::SUCCESS_SCAN) {
            $response->setQuestionTask($this->apiQuestService->getCurrentQuestionTask());
        }

        return new Response(['task_result' => $response]);
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
        $response = new QuestQuestionTaskResponse();

        $questStatus = (new QuestStatus())
            ->setNumber(4)
            ->setTotalCount(7)
            ->setPrevTasks([true, false, true]);

        $prizes = [];

        $prizes[] = (new Prize())
            ->setId(1)
            ->setName('Лакомство')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/360/360004ada6f462b0b2eeb0c92c69a08a.jpg');

        $prizes[] = (new Prize())
            ->setId(2)
            ->setName('Игрушка')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/44b/44bb31933296cd569313f298a50250f1.jpg');

        $barcodeTask = (new BarcodeTask())
            ->setTask('Найди в магазине отдел сухого корма для собак и отсканируй штрихкод на любом сухом корме Грандин!')
            ->setTitle('Основа правильного питания')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/d69/d691aca429186f820ffb8415203b0956.jpg');


        $response
            ->setCorrect(true)
            ->setErrorText('поясняющий текст')
            ->setBarcodeTask($barcodeTask)
            ->setPrizes($prizes)
            ->setQuestStatus($questStatus);

        return $response;
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
        $response = new QuestPrizeResponse();

        $response
            ->setPromocode('TEST_TEST');

        return $response;
    }
}
