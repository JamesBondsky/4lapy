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
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use function serialize;
use function unserialize;

class ChanceService
{
    protected const CHANCE_RATE = 500;

    protected const HL_BLOCK_NAME = 'NewYearUserChance';

    public const PERIODS = [
        [
            'from' => '01.10.2019 00:00:00',
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

        foreach (self::PERIODS as $period) {
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
     * @return int
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws Exception
     */
    public function registerUser(Request $request): int
    {
        $currentPeriod = $this->getCurrentPeriod();
        $user = $this->userService->getCurrentUser();

        if ($this->getDataManager()::query()->setFilter(['UF_USER_ID' => $user->getId()])->exec()->fetch()) {
            throw new RuntimeException('Пользователь уже зарегистрирован');
        }

        if ($this->updateUserFields($request, $user) && !$this->userRepository->update($user)) {
            throw new RuntimeException('При регистрации произошла ошибка');
        }

        $data = [];
        foreach ($this->periods as $period) {
            $data[$period] = 0;
        }

        $data[$currentPeriod] = $this->getUserPeriodChance($user->getId(), $currentPeriod);

        $addResult = $this->getDataManager()::add([
            'UF_USER_ID' => $user->getId(),
            'UF_DATA' => serialize($data),
            'UF_DATE_CREATE' => new Date(),
        ]);

        if (!$addResult->isSuccess()) {
            throw new RuntimeException('При регистрации произошла ошибка');
        }

        return $data[$currentPeriod];
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws SystemException
     * @throws Exception
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
            return $userData[$this->getCurrentPeriod()];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return bool
     * @throws InvalidArgumentException
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
     * @return int
     * @throws NotFoundException
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
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUserPeriodChance($userId, $period): int
    {
        $sum = 0;

        $res = OrderTable::query()
            ->setFilter([
                'USER_ID' => $userId,
                '>=DATE_INSERT' => self::PERIODS[$period]['from'],
                '<=DATE_INSERT' => self::PERIODS[$period]['to']
            ])
            ->setSelect(['ID', 'PRICE'])
            ->exec();

        while ($order = $res->fetch()) {
            $sum += (float)$order['PRICE'];
        }

        return (int)floor($sum / self::CHANCE_RATE);
    }

    public function updateUserChance(): void
    {
        try {
            $currentPeriod = $this->getCurrentPeriod();
            $userId = $this->userService->getCurrentUserId();

            $userResult = $this->getDataManager()::query()
                ->setFilter(['UF_USER_ID' => $userId])
                ->setSelect(['ID', 'UF_DATA'])
                ->exec()->fetch();

            if (!$userResult) {
                return;
            }

            $data = unserialize($userResult['UF_DATA']);

            $data[$this->getCurrentPeriod()] = $this->getUserPeriodChance($userId, $currentPeriod);

            $this->getDataManager()::update(
                $userResult['ID'],
                ['UF_DATA' => serialize($data)]
            );

        } catch (Exception $e) {
        }
    }

    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @return DataManager
     * @throws Exception
     */
    protected function getDataManager(): DataManager
    {
        if ($this->dataManager === null) {
            $this->dataManager = HLBlockFactory::createTableObject(self::HL_BLOCK_NAME);
        }

        return $this->dataManager;
    }
}
