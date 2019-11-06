<?php

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\FileTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\BitrixOrm\Model\Interfaces\ImageInterface;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use FourPaws\MobileApiBundle\Dto\Object\Quest\AnswerVariant;
use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestionTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\QuestBarcodeRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestQuestionRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestStartRequest;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterGetResponse;
use FourPaws\MobileApiBundle\Exception\AccessDeinedException;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\MobileApiBundle\Exception\RuntimeException as ApiRuntimeException;
use Symfony\Component\HttpFoundation\Request;

class QuestService
{
    protected const QUEST_CODE = 'QUEST';

    protected const PET_HL_NAME = 'QuestPet';
    protected const PRIZE_HL_NAME = 'QuestPrize';
    protected const RESULT_HL_NAME = 'QuestResult';
    protected const TASK_HL_NAME = 'QuestTask';

    /**
     * @var ApiProductService
     */
    private $apiProductService;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var UserService
     */
    protected $apiUserService;

    /**
     * @var UserSearchInterface
     */
    protected $appUserService;

    /**
     * @var DataManager[]
     */
    protected $dataManagers;

    /**
     * @var User|null
     */
    protected $currentUser;

    /**
     * @var array|null
     */
    protected $currentUserResult;

    /**
     * @var array|null
     */
    protected $currentTask;

    /**
     * QuestService constructor.
     * @param ProductService $apiProductService
     * @param ImageProcessor $imageProcessor
     * @param UserService $apiUserService
     * @param UserSearchInterface $appUserService
     */
    public function __construct(
        ApiProductService $apiProductService,
        ImageProcessor $imageProcessor,
        UserService $apiUserService,
        UserSearchInterface $appUserService
    )
    {
        $this->apiProductService = $apiProductService;
        $this->imageProcessor = $imageProcessor;
        $this->apiUserService = $apiUserService;
        $this->appUserService = $appUserService;
    }

    /**
     * @return QuestRegisterGetResponse
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getQuestRegisterStatus(): QuestRegisterGetResponse
    {
        $userResult = $this->getUserResult();
        $result = (new QuestRegisterGetResponse())
            ->setNeedRegister(($userResult === null))
            ->setHasEmail(!empty($this->getCurrentUser()->getEmail()))
            ->setUserEmail($this->getCurrentUser()->getEmail());

        if (!$result->isNeedRegister()) {
            $finishTest = true;

            foreach (unserialize($userResult['UF_TASKS']) as $userTask) {
                if ($userTask['QUESTION_RESULT'] === QuestionTask::STATUS_NOT_START) {
                    $finishTest = false;
                }
            }

            if ($finishTest) {
                /** @var Pet $userPet */
                $userPet = current($this->getPetTypes([$userResult['UF_PET']]));
                $result
                    ->setIsFinishStep(true)
                    ->setPrizes($userPet->getPrizes());
            } else {
                $result->setNeedChoosePet(true);
                $result->setPetTypes($this->getPetTypes());
            }
        }

        return $result;
    }

    /**
     * @param QuestRegisterRequest $questRegisterRequest
     * @return void
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws AccessDeinedException
     * @throws Exception
     */
    public function registerUser(QuestRegisterRequest $questRegisterRequest): void
    {
        $user = $this->getCurrentUser();

        $needUpdateUser = false;

        if ($userEmail = $user->getEmail()) {
            if ($userEmail !== $questRegisterRequest->getEmail()) {
                throw new AccessDeinedException('Ошибка в валидации электронной почты');
            }
        } else {
            $email = $questRegisterRequest->getEmail();

            try {
                $this->appUserService->findOneByEmail($email);
                throw new AccessDeinedException('Введеный вами email уже используется');
            } catch (NotFoundException $e) {
            }

            $user->setEmail($email);
        }

        if ($questRegisterRequest->getCode() !== self::QUEST_CODE) {
            throw new AccessDeinedException('Введите корректный код');
        }

        if ($needUpdateUser) {
            $expertSender = Application::getInstance()->getContainer()->get('expertsender.service');
            // todo обновить пользователя и послать письмо с подтвержение почты
        }

        $userResult = $this->getUserResult();

        if ($userResult !== null) {
            $this->getDataManager(self::RESULT_HL_NAME)::update($userResult['ID'], [
                'UF_PET' => null,
                'UF_TASKS' => null,
                'UF_CURRENT_TASK' => 0,
            ]);
        } else {
            $this->getDataManager(self::RESULT_HL_NAME)::add([
                'UF_USER_ID' => $user->getId(),
            ]);
        }
    }

    /**
     * @param QuestStartRequest $questStartRequest
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws AccessDeinedException
     * @throws Exception
     */
    public function startQuest(QuestStartRequest $questStartRequest): void
    {
        $userResult = $this->getUserResult();

        $pets = $this->getPetTypes([$questStartRequest->getPetTypeId()]);

        if (!isset($pets[$questStartRequest->getPetTypeId()])) {
            throw new AccessDeinedException('Неккоректный ID питомца');
        }

        /** @var Pet $userPet */
        $userPet = $pets[$questStartRequest->getPetTypeId()];

        $userResult['UF_PET'] = $userPet->getId();
        $userResult['UF_TASKS'] = serialize($this->generateTasks($userPet->getId()));
        $userResult['UF_CURRENT_TASK'] = 1;

        $this->updateCurrentUserResult($userResult);
    }

    /**
     * @param QuestBarcodeRequest $questBarcodeRequest
     * @return int
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function checkBarcodeTask(QuestBarcodeRequest $questBarcodeRequest): int
    {
        $productList = $this->apiProductService->getList(new Request(), 0, 'relevance', 1, 1, $questBarcodeRequest->getBarcode());

        $product = null;
        if ($currentProduct = $productList->current()) {
            /** @var FullProduct $product */
            $product = $currentProduct[0];
        }

        if ($product === null) {
            return BarcodeTask::SCAN_ERROR;
        }

        $offerCollection = (new OfferQuery())->withFilter(['=ID' => $product->getId()])->exec();

        if ($offerCollection->isEmpty()) {
            return BarcodeTask::SCAN_ERROR;
        }

        $currentTask = $this->getCurrentTask();

        /** @var Offer $offer */
        $offer = $offerCollection->first();

        if (in_array($currentTask['UF_CATEGORY'], $offer->getProduct()->getSectionsIdList(), false)) {
            $userResult = $this->getUserResult();

            if ($userResult === false) {
                throw new ApiRuntimeException('Начните проходить квест');
            }

            $userTasks = unserialize($userResult['UF_TASKS']);

            if (!isset($userTasks[$userResult['UF_CURRENT_TASK']]['ID'])) {
                throw new ApiRuntimeException('Задание не найдено');
            }

            $userTasks[$userResult['UF_CURRENT_TASK']]['BARCODE_COMPLETE'] = true;

            $userResult['UF_TASKS'] = serialize($userTasks);

            $this->updateCurrentUserResult($userResult);

            return BarcodeTask::SUCCESS_SCAN;
        }

        return BarcodeTask::INCORRECT_PRODUCT;
    }

    /**
     * @param QuestQuestionRequest $questQuestionRequest
     * @return bool
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function checkQuestionTask(QuestQuestionRequest $questQuestionRequest): bool
    {
        $userResult = $this->getUserResult();

        if ($userResult === false) {
            throw new ApiRuntimeException('Начните проходить квест');
        }

        $userTasks = unserialize($userResult['UF_TASKS']);

        if (!isset($userTasks[$userResult['UF_CURRENT_TASK']]['ID'])) {
            throw new ApiRuntimeException('Задание не найдено');
        }

        if ($userTasks[$userResult['UF_CURRENT_TASK']]['BARCODE_COMPLETE'] === false) {
            throw new ApiRuntimeException('Выполните предыдущее задание');
        }

        $currentTask = $this->getCurrentTask();

        $userAnswer = null;

        foreach ($currentTask['UF_VARIANTS'] as $key => $variant) {
            if ($questQuestionRequest->getVariantId() === $key) {
                $userAnswer = $variant;
            }
        }

        if ($userAnswer === null) {
            throw new ApiRuntimeException('Не найден вариант ответа');
        }

        $correctAnswer = ($userAnswer === $currentTask['UF_ANSWER']);

        $userTasks[$userResult['UF_CURRENT_TASK']]['QUESTION_RESULT'] = ($correctAnswer) ? QuestionTask::STATUS_SUCCESS_COMPLETE : QuestionTask::STATUS_FAIL_COMPLETE;

        $userResult['UF_TASKS'] = serialize($userTasks);
        ++$userResult['UF_CURRENT_TASK'];

        $this->updateCurrentUserResult($userResult);

        return $correctAnswer;
    }

    /**
     * @return User
     *
     * @throws AccessDeinedException
     */
    public function getCurrentUser(): User
    {
        if ($this->currentUser === null) {
            try {
                $this->currentUser = $this->apiUserService->getCurrentApiUser();
            } catch (Exception $e) {
            }

            if ($this->currentUser === null) {
                throw new AccessDeinedException('Авторизуйтесь для участия в квесте');
            }
        }

        return $this->currentUser;
    }

    /**
     * @param bool $reload
     * @return array|null
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getUserResult(bool $reload = false): ?array
    {
        if ($this->currentUserResult === null || $reload) {
            $result = $this->getDataManager(self::RESULT_HL_NAME)::query()
                ->setFilter(['=UF_USER_ID' => $this->getCurrentUser()->getId()])
                ->setSelect(['ID', 'UF_PET', 'UF_TASKS', 'UF_CURRENT_TASK'])
                ->exec()
                ->fetch();

            if ($result !== false) {
                $this->currentUserResult = $result;
            }
        }

        return $this->currentUserResult;
    }

    /**
     * @param $userResult
     *
     * @throws Exception
     */
    protected function updateCurrentUserResult($userResult): void
    {
        $updateResult = $this->getDataManager(self::RESULT_HL_NAME)::update($userResult['ID'], $userResult);
        if ($updateResult->isSuccess()) {
            $this->currentUserResult = $userResult;
        }
    }

    /**
     * @param bool $reload
     * @return array|false
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getCurrentTask(bool $reload = false): array
    {
        if ($this->currentTask === null || $reload) {
            $userResult = $this->getUserResult();

            if ($userResult === false) {
                throw new ApiRuntimeException('Начните проходить квест');
            }

            $userTasks = unserialize($userResult['UF_TASKS']);

            if (!isset($userTasks[$userResult['UF_CURRENT_TASK']]['ID'])) {
                throw new ApiRuntimeException('Задание не найдено');
            }

            $currentTask = $this->getDataManager(self::TASK_HL_NAME)::query()
                ->setSelect(['ID', 'UF_TITLE', 'UF_TASK', 'UF_IMAGE', 'UF_VARIANTS', 'UF_ANSWER', 'UF_QUESTION', 'UF_CATEGORY', 'UF_CORRECT_TEXT', 'UF_BARCODE_ERROR', 'UF_QUESTION_ERROR'])
                ->setFilter(['=ID' => $userTasks[$userResult['UF_CURRENT_TASK']]['ID']])
                ->exec()
                ->fetch();

            if ($currentTask === false) {
                throw new ApiRuntimeException('Задание не найдено');
            }

            $this->currentTask = $currentTask;
        }

        return $this->currentTask;
    }

    /**
     * @return BarcodeTask
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getCurrentBarcodeTask(): BarcodeTask
    {
        $currentTask = $this->getCurrentTask();

        $image = null;
        if ($currentTask['UF_IMAGE']) {

            $item = FileTable::query()->addFilter('=ID', $currentTask['UF_IMAGE'])->addSelect('*')->exec()->fetch();
            if ($item === false) {
                $item = null;
            } else {
                $image = (new Image($item))->getSrc();
            }
        }

        return (new BarcodeTask())
            ->setTask($currentTask['UF_TASK'])
            ->setTitle($currentTask['UF_TITLE'])
            ->setImage($image);
    }

    /**
     * @return QuestionTask
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getCurrentQuestionTask(): QuestionTask
    {
        $currentTask = $this->getCurrentTask();
        $variants = [];

        foreach ($currentTask['UF_VARIANTS'] as $key => $variant) {
            $variants[] = (new AnswerVariant())
                ->setId($key)
                ->setTitle($variant);
        }

        return (new QuestionTask())
            ->setQuestion($currentTask['UF_QUESTION'])
            ->setVariants($variants);
    }

    /**
     * @return QuestStatus
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getQuestStatus(): QuestStatus
    {
        $userResult = $this->getUserResult();

        $taskResult = unserialize($userResult['UF_TASKS']);

        $prevResult = [];

        foreach ($taskResult as $result) {
            if ($result['QUESTION_RESULT'] === QuestionTask::STATUS_SUCCESS_COMPLETE) {
                $prevResult[] = true;
            } else if ($result['QUESTION_RESULT'] === QuestionTask::STATUS_FAIL_COMPLETE) {
                $prevResult[] = false;
            }
        }

        return (new QuestStatus())
            ->setNumber($userResult['UF_CURRENT_TASK'])
            ->setTotalCount(count($taskResult))
            ->setPrevTasks($prevResult);
    }

    /**
     * @param array $petTypeId
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getPetTypes(array $petTypeId = []): array
    {
        $result = [];
        $pets = [];
        $imageIds = [];
        $prizeIds = [];

        $res = $this->getDataManager(self::PET_HL_NAME)::query()
            ->setSelect(['ID', 'UF_NAME', 'UF_IMAGE', 'UF_DESCRIPTION', 'UF_PRIZES']);

        if ($petTypeId && !empty($petTypeId)) {
            $res->setFilter(['ID' => $petTypeId]);
        }

        $res->exec();

        foreach ($res->fetchAll() as $pet) {
            $pets[$pet['ID']] = $pet;

            if ($pet['UF_IMAGE']) {
                $imageIds[] = $pet['UF_IMAGE'];
            }

            if ($pet['UF_PRIZES']) {
                foreach ($pet['UF_PRIZES'] as $prizeId) {
                    $prizeIds[] = $prizeId;
                }
            }
        }

        $imageCollection = ImageCollection::createFromIds($imageIds);

        $prizes = $this->getPrizes($prizeIds);

        foreach ($pets as $pet) {
            $petPrizes = [];
            foreach ($pet['UF_PRIZES'] as $prizeId) {
                if (isset($prizes[$prizeId])) {
                    $petPrizes[] = $prizes[$prizeId];
                }
            }

            $result[$pet['ID']] = (new Pet())
                ->setId($pet['ID'])
                ->setTitle($pet['UF_NAME'])
                ->setDescription($pet['UF_DESCRIPTION'])
                ->setImage($this->getImageFromCollection($pet['UF_IMAGE'], $imageCollection))
                ->setPrizes($petPrizes);
        }

        return $result;
    }

    /**
     * @param array $prizeIds
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getPrizes(array $prizeIds = []): array
    {
        $result = [];
        $prizes = [];
        $imageIds = [];

        if ((!$prizeIds || empty($prizeIds))) {
            return [];
        }

        $res = $this->getDataManager(self::PRIZE_HL_NAME)::query()
            ->setFilter(['=ID' => $prizeIds])
            ->setSelect(['ID', 'UF_NAME', 'UF_IMAGE'])
            ->exec();

        foreach ($res as $prize) {
            if ($prize['UF_IMAGE']) {
                $imageIds[] = $prize['UF_IMAGE'];
            }

            $prizes[$prize['ID']] = $prize;
        }

        $imageCollection = ImageCollection::createFromIds($imageIds);

        foreach ($prizes as $prize) {
            $result[$prize['ID']] = (new Prize())
                ->setId($prize['ID'])
                ->setName($prize['UF_NAME'])
                ->setImage($this->getImageFromCollection($prize['UF_IMAGE'], $imageCollection));
        }

        return $result;
    }

    /**
     * @param $imageId
     * @param $imageCollection
     * @return ImageInterface|null
     *
     */
    protected function getImageFromCollection($imageId, $imageCollection): ?ImageInterface
    {
        return $this->imageProcessor->findImage($imageId, $imageCollection);
    }

    /**
     * @param $petTypeId
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    protected function generateTasks($petTypeId): array
    {
        $res = $this->getDataManager(self::TASK_HL_NAME)::query()
            ->setFilter(['=UF_PET' => $petTypeId])
            ->setSelect(['ID'])
            ->exec();

        $tasks = [];
        foreach ($res as $task) {
            $tasks[] = [
                'ID' => $task['ID'],
                'BARCODE_COMPLETE' => false,
                'QUESTION_RESULT' => QuestionTask::STATUS_NOT_START
            ];
        }

        shuffle($tasks);
        $number = 1;

        $result = [];

        foreach ($tasks as $task) {
            $result[$number] = $task;
            $number++;
        }

        return $result;
    }

    /**
     * @param $entityName
     * @return DataManager
     *
     * @throws Exception
     */
    protected function getDataManager($entityName): DataManager
    {
        if (!isset($this->dataManagers[$entityName])) {
            $this->dataManagers[$entityName] = HLBlockFactory::createTableObject($entityName);
        }

        return $this->dataManagers[$entityName];
    }
}
