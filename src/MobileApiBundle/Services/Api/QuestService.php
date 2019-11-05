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
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\BitrixOrm\Model\Interfaces\ImageInterface;
use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestionTask;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;
use FourPaws\MobileApiBundle\Dto\Request\QuestStartRequest;
use FourPaws\MobileApiBundle\Dto\Response\QuestRegisterGetResponse;
use FourPaws\MobileApiBundle\Exception\AccessDeinedException;
use FourPaws\MobileApiBundle\Exception\NotFoundUserException;
use FourPaws\UserBundle\Exception\EmptyPhoneException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\MobileApiBundle\Exception\RuntimeException as ApiRuntimeException;

class QuestService
{
    protected const QUEST_CODE = 'QUEST';

    protected const PET_HL_NAME = 'QuestPet';
    protected const PRIZE_HL_NAME = 'QuestPrize';
    protected const RESULT_HL_NAME = 'QuestResult';
    protected const TASK_HL_NAME = 'QuestTask';

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
     * QuestService constructor.
     * @param ImageProcessor $imageProcessor
     * @param UserService $apiUserService
     * @param UserSearchInterface $appUserService
     */
    public function __construct(ImageProcessor $imageProcessor, UserService $apiUserService, UserSearchInterface $appUserService)
    {
        $this->imageProcessor = $imageProcessor;
        $this->apiUserService = $apiUserService;
        $this->appUserService = $appUserService;
    }

    /**
     * @return QuestRegisterGetResponse
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getQuestStatus(): QuestRegisterGetResponse
    {
        $userResult = $this->getCurrentUserResult();
        $result = (new QuestRegisterGetResponse())
            ->setNeedRegister(($userResult === null))
            ->setHasEmail(!empty($this->getCurrentUser()->getEmail()))
            ->setUserEmail($this->getCurrentUser()->getEmail());

        if (!$result->isNeedRegister()) {
            $result->setNeedChoosePet(true);
            $result->setPetTypes($this->getPetTypes());
        }


        return $result;
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
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws AccessDeinedException
     * @throws Exception
     */
    protected function getCurrentUserResult(): ?array
    {
        if ($this->currentUserResult === null) {
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
     * @param QuestRegisterRequest $questRegisterRequest
     * @return void
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

        $userResult = $this->getCurrentUserResult();

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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws AccessDeinedException
     * @throws Exception
     */
    public function startQuest(QuestStartRequest $questStartRequest): void
    {
        $userResult = $this->getCurrentUserResult();

        $pets = $this->getPetTypes([$questStartRequest->getPetTypeId()]);

        if (!isset($pets[$questStartRequest->getPetTypeId()])) {
            throw new AccessDeinedException('Неккоректный ID питомца');
        }

        /** @var Pet $pet */
        $pet = $pets[$questStartRequest->getPetTypeId()];

        $userResult['UF_PET'] = $pet->getId();
        $userResult['UF_TASKS'] = serialize($this->generateTasks($pet->getId()));
        $userResult['UF_CURRENT_TASK'] = 1;

        $this->updateCurrentUserResult($userResult);
    }

    /**
     * @return BarcodeTask
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getCurrentBarcodeTask(): BarcodeTask
    {
        $userResult = $this->getCurrentUserResult();

        if ($userResult === false) {
            throw new ApiRuntimeException('Начните проходить квест');
        }

        $tasks = unserialize($userResult['UF_TASKS']);

        if (!isset($tasks[$userResult['UF_CURRENT_TASK']]['ID'])) {
            throw new ApiRuntimeException('Задание не найдено');
        }

        $barcodeTask = $this->getDataManager(self::TASK_HL_NAME)::query()
            ->setSelect(['ID', 'UF_TITLE', 'UF_TASK', 'UF_IMAGE'])
            ->setFilter(['=ID' => $tasks[$userResult['UF_CURRENT_TASK']]['ID']])
            ->exec()
            ->fetch();

        if ($barcodeTask === false) {
            throw new ApiRuntimeException('Задание не найдено');
        }

        $image = null;
        if ($barcodeTask['UF_IMAGE']) {

            $item = FileTable::query()->addFilter('=ID', $barcodeTask['UF_IMAGE'])->addSelect('*')->exec()->fetch();
            if ($item === false) {
                $item = null;
            } else {
                $image = (new Image($item))->getSrc();
            }
        }

        return (new BarcodeTask())
            ->setTask($barcodeTask['UF_TASK'])
            ->setTitle($barcodeTask['UF_TITLE'])
            ->setImage($image);
    }

    /**
     * @param array $petTypeId
     * @return array
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
                ->setName($pet['UF_NAME'])
                ->setDescription($pet['UF_DESCRIPTION'])
                ->setImage($this->getImageFromCollection($pet['UF_IMAGE'], $imageCollection))
                ->setPrizes($petPrizes);
        }

        return $result;
    }

    /**
     * @param array $prizeIds
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getPrizes(array $prizeIds): array
    {
        if (!$prizeIds || empty($prizeIds)) {
            return [];
        }

        $result = [];
        $prizes = [];
        $imageIds = [];

        $res = $this->getDataManager(self::PRIZE_HL_NAME)::query()
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
     */
    protected function getImageFromCollection($imageId, $imageCollection): ?ImageInterface
    {
        return $this->imageProcessor->findImage($imageId, $imageCollection);
    }

    /**
     * @param $entityName
     * @return DataManager
     * @throws Exception
     */
    protected function getDataManager($entityName): DataManager
    {
        if (!isset($this->dataManagers[$entityName])) {
            $this->dataManagers[$entityName] = HLBlockFactory::createTableObject($entityName);
        }

        return $this->dataManagers[$entityName];
    }

    /**
     * @param $petTypeId
     * @return array
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
}
