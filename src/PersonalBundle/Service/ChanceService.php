<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Sale\OrderTable;
use DateTime;
use Exception;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;
use function serialize;
use function unserialize;

class ChanceService
{
    protected const CHANCE_RATE = 500;

    protected const HL_BLOCK_NAME = 'NewYearUserChance';

    protected const CACHE_TAG = 'ny2020:user.chances';

    public const PERIODS = [
        [
            'from' => '01.12.2019 00:00:00',
            'to' => '08.12.2019 23:59:59',
        ],
        [
            'from' => '09.12.2019 00:00:00',
            'to' => '15.12.2019 23:59:59',
        ],
        [
            'from' => '16.12.2019 00:00:00',
            'to' => '22.12.2019 23:59:59',
        ],
        [
            'from' => '23.12.2019 00:00:00',
            'to' => '29.12.2019 23:59:59',
        ],
    ];

    /** @var CurrentUserProviderInterface */
    protected $userService;

    /** @var UserRepository */
    protected $userRepository;

    protected $periods = [];

    protected $currentPeriod;

    /** @var DataManager */
    protected $dataManager;

    public function __construct(CurrentUserProviderInterface $userService, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;

        foreach (static::PERIODS as $period) {
            $this->periods[] = [
                'from' => DateTime::createFromFormat('d.m.Y H:i:s', $period['from']),
                'to' => DateTime::createFromFormat('d.m.Y H:i:s', $period['to']),
            ];
        }

        $currentTimestamp = (new DateTime())->getTimestamp();
        foreach ($this->periods as $key => $period) {
            if (($currentTimestamp > $period['from']->getTimestamp()) && ($currentTimestamp < $period['to']->getTimestamp())) {
                $this->currentPeriod = $key;
            }
        }
    }

    /**
     * @param Request $request
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws Exception
     * @return int
     */
    public function registerUser(Request $request): int
    {
        $user = $this->userService->getCurrentUser();

        if ($this->getDataManager()::query()->setFilter(['UF_USER_ID' => $user->getId()])->exec()->fetch()) {
            throw new RuntimeException('Пользователь уже зарегистрирован');
        }

        if ($this->updateUserFields($request, $user) && !$this->userRepository->update($user)) {
            throw new RuntimeException('При регистрации произошла ошибка');
        }

        $data = [];
        foreach ($this->periods as $periodId => $period) {
            $data[$periodId] = 0;
        }

        $addResult = $this->getDataManager()::add([
            'UF_USER_ID' => $user->getId(),
            'UF_DATA' => serialize($data),
            'UF_DATE_CREATE' => new Date(),
        ]);

        if (!$addResult->isSuccess()) {
            throw new RuntimeException('При регистрации произошла ошибка');
        }

        TaggedCacheHelper::clearManagedCache([static::CACHE_TAG]);

        /** @var ManzanaService $manzanaService */
        $manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
        $manzanaService->importUserOrdersAsync($user);

        return 0;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws SystemException
     * @throws Exception
     * @return int
     */
    public function getCurrentUserChances(): int
    {
        $userId = $this->userService->getCurrentUserId();

        try {
            if (!$userData = $this->getDataManager()::query()->setFilter(['UF_USER_ID' => $userId])->setSelect(['UF_DATA'])->exec()->fetch()) {
                throw new RuntimeException('Пользователь не зарегистрирован');
            }
        } catch (RuntimeException $e) {
            throw $e;
        }

        try {
            $userData = unserialize($userData['UF_DATA']);
            return $userData[$this->getCurrentPeriod()] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @throws InvalidArgumentException
     * @return bool
     */
    protected function updateUserFields(Request $request, User $user): bool
    {
        $update = false;
        $fields = ['Name', 'LastName', 'Email'];

        foreach ($fields as $field) {
            if (empty($user->{"get$field"}())) {
                $value = $request->get(strtolower($field), '');

                if (empty($value)) {
                    throw new InvalidArgumentException('Заполнте все поля');
                }

                $user->{"set$field"}($value);
                $update = true;
            }
        }

        return $update;
    }

    /**
     * @throws NotFoundException
     * @return int
     */
    public function getCurrentPeriod(): int
    {
        if ($this->currentPeriod === null) {
            throw new NotFoundException('Акция закончилась или еще не начилась');
        }

        return $this->currentPeriod;
    }

    /**
     * @param $userId
     * @param $period
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return int
     */
    public function getUserPeriodChance($userId, $period): int
    {
        $sum = 0;

        $res = OrderTable::query()
            ->setFilter([
                'USER_ID' => $userId,
                '>=DATE_INSERT' => static::PERIODS[$period]['from'],
                '<=DATE_INSERT' => static::PERIODS[$period]['to'],
                'STATUS_ID' => [
                    OrderStatus::STATUS_DELIVERED,
                    OrderStatus::STATUS_FINISHED,
                ],
            ])
            ->setSelect(['ID', 'PRICE'])
            ->exec();

        while ($order = $res->fetch()) {
            $sum += (float)$order['PRICE'];
        }

        return (int)floor($sum / static::CHANCE_RATE);
    }

    public function updateUserChance($userId): void
    {
        if (!$userId || empty($userId)) {
            return;
        }

        try {
            $userResult = $this->getDataManager()::query()
                ->setFilter(['UF_USER_ID' => $userId])
                ->setSelect(['ID', 'UF_DATA'])
                ->exec()->fetch();

            if (!$userResult) {
                return;
            }

            $data = unserialize($userResult['UF_DATA']);

            foreach ($this->periods as $periodId => $currentPeriod) {
                $data[$periodId] = $this->getUserPeriodChance($userId, $periodId);
            }

            $this->getDataManager()::update(
                $userResult['ID'],
                ['UF_DATA' => serialize($data)]
            );
        } catch (Exception $e) {
        }
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function updateAllUserChance(): void
    {
        $res = $this->getDataManager()::query()
            ->setSelect(['UF_USER_ID'])
            ->exec();

        while ($user = $res->fetch()) {
            $this->updateUserChance($user['UF_USER_ID']);
        }
    }

    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * Нужно, чтобы импорт заказов из манзаны работал только для пользователей акции
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllUserIds($offset = 0, $limit = 1): array
    {
//        $doGetAllVariants = function () {
        try {
            $userIds = [];
            $res = $this->getDataManager()::query()
                ->setOrder(['UF_USER_ID' => 'ASC'])
                ->setSelect(['UF_USER_ID'])
                ->setOffset($offset)
                ->setLimit($limit)
                ->exec();

            while ($userResult = $res->fetch()) {
                $userIds[] = (int)$userResult['UF_USER_ID'];
            }

            return $userIds;
        } catch (Exception $e) {
            return [];
        }
//        };
//
//        try {
//            return (new BitrixCache())
//                ->withId(__METHOD__ . 'chance.users')
//                ->withTime(36000)
//                ->withTag(static::CACHE_TAG)
//                ->resultOf($doGetAllVariants);
//        } catch (Exception $e) {
//            return [];
//        }
    }

    /**
     * @return array
     */
    public function getExportHeader(): array
    {
        $result = [
            'Дата регистрации',
            'ФИО',
            'Телефон',
            'Почта',
        ];

        foreach ($this->periods as $periodId => $period) {
            $result[] = sprintf('%s - %s', $period['from']->format('d.m.Y'), $period['to']->format('d.m.Y'));
        }

        $result[] = 'Всего';

        return $result;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function getExportData(): array
    {
        $userResults = [];
        $res = $this->getDataManager()::query()
            ->setSelect(['UF_USER_ID', 'UF_DATA', 'UF_DATE_CREATE'])
            ->exec();

        while ($userResult = $res->fetch()) {
            $userResults[] = [
                'userId' => $userResult['UF_USER_ID'],
                'data' => unserialize($userResult['UF_DATA']),
                'date' => $userResult['UF_DATE_CREATE'],
            ];
        }

        $result = [];

        foreach ($userResults as $userResult) {
            $users = $this->userRepository->findBy(['=ID' => $userResult['userId']]);

            $user = (count($users) > 0) ? current($users) : null;

            $tmpResult = [];
            $tmpResult[] = $userResult['date'];
            $tmpResult[] = ($user !== null) ? $user->getFullName() : 'user not found';
            $tmpResult[] = ($user !== null) ? $user->getPersonalPhone() : 'user not found';
            $tmpResult[] = ($user !== null) ? $user->getEmail() : 'user not found';

            $totalChances = 0;

            foreach ($this->periods as $periodId => $period) {
                if (isset($userResult['data'][$periodId])) {
                    $tmpResult[] = $userResult['data'][$periodId];
                    $totalChances += (int)$userResult['data'][$periodId];
                } else {
                    $tmpResult[] = '-';
                }
            }

            $tmpResult[] = $totalChances;

            $result[] = $tmpResult;
        }

        return $result;
    }

    /**
     * @throws Exception
     * @return DataManager
     */
    protected function getDataManager(): DataManager
    {
        if ($this->dataManager === null) {
            $this->dataManager = HLBlockFactory::createTableObject(static::HL_BLOCK_NAME);
        }

        return $this->dataManager;
    }
}
