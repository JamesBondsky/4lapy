<?php

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Object\Quest\AnswerVariant;
use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestionTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;
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
        $response = new QuestRegisterGetResponse();
        $response
            ->setNeedRegister(true)
            ->setHasEmail(true)
            ->setUserEmail('test@test.test');

        return $response;
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
        $response = new QuestRegisterPostResponse();

        $petTypes = [];

        $prizes = [];

        $prizes[] = (new Prize())
            ->setId(1)
            ->setName('Лакомство')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/360/360004ada6f462b0b2eeb0c92c69a08a.jpg');

        $prizes[] = (new Prize())
            ->setId(2)
            ->setName('Игрушка')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/44b/44bb31933296cd569313f298a50250f1.jpg');


        $petTypes[] = (new Pet())
            ->setId(1)
            ->setName('Кошка')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/332/33251b91dd0fc09c0c7b650555ba6d9b.jpg')
            ->setDescription("1. Пройди квест и стань самым лучшим хозяином для своей кошки\n\n
            2. Найди 7 товаров в магазине.\n
            Отсканирую штрихкод\n
            Узнай любопытный факт\n
            Получи подарок!\n\n
            3. Время прохождения: 5 минут\n\n
            Приз за прохождение: Лакомство или игрушка для кошек")
            ->setPrizes($prizes);

        $prizes = [];

        $prizes[] = (new Prize())
            ->setId(3)
            ->setName('Лакомство')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/e0b/e0bfb55cf5ec5c9a6c56e0dcf6db0146.jpg');

        $prizes[] = (new Prize())
            ->setId(4)
            ->setName('Игрушка')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/3a0/3a0f4960dffe56ece31ec8b98e3a7af1.jpg');

        $petTypes[] = (new Pet())
            ->setId(2)
            ->setName('Собака')
            ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/a5c/a5c3d99c0137b39ef621269c234b0fdd.jpg')
            ->setDescription("1. Пройди квест и стань самым лучшим хозяином для своей собаки\n\n
            2. Найди 7 товаров в магазине.\n
            Отсканирую штрихкод\n
            Узнай любопытный факт\n
            Получи подарок!\n\n
            3. Время прохождения: 5 минут\n\n
            Приз за прохождение: Лакомство или игрушка для собак")
            ->setPrizes($prizes);

        $response
            ->setPetTypes($petTypes);


        return $response;
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
        $response = new QuestStartResponse();

        $response->setBarcodeTask((new BarcodeTask())
        ->setTask('Найди в магазине отдел сухого корма для собак и отсканируй штрихкод на любом сухом корме Грандин!')
        ->setTitle('Основа правильного питания')
        ->setImage('https://4lapy.ru/resize/240x240/upload/iblock/d69/d691aca429186f820ffb8415203b0956.jpg'));

        return $response;
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
        $response = new QuestBarcodeTaskResponse();

        $variants = [];

        $variants[] = (new AnswerVariant())
            ->setId(1)
            ->setVariant('Раз');

        $variants[] = (new AnswerVariant())
            ->setId(2)
            ->setVariant('2 Раза');

        $variants[] = (new AnswerVariant())
            ->setId(3)
            ->setVariant('3 Раза');

        $questionTask = (new QuestionTask())
            ->setQuestion('Знаете ли вы, сколько раз в день необходимо кормить взрослую собаку сухим кормом?')
            ->setVariants($variants);

        $questStatus = (new QuestStatus())
        ->setNumber(3)
        ->setTotalCount(7)
        ->setPrevTasks([true, false]);

        $response
            ->setResult(2)
            ->setCorrectText('Чтобы приготовить 1 кг сухого корма на произодстве используется 3 кг натурального мяса!')
            ->setErrorText('поясняющий текст')
            ->setQuestionTask($questionTask)
            ->setQuestStatus($questStatus);

        return $response;
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
