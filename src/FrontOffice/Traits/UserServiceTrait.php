<?php

namespace FourPaws\FrontOffice\Traits;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserService;
use Bitrix\Main\SystemException;
use FourPaws\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;

trait UserServiceTrait
{
    /** @var UserService $userService */
    private $userService;

    /** @var bool $userServiceMethodsLogEnabled */
    protected $userServiceMethodsLogEnabled = false;

    /**
     * @return UserService
     * @throws ApplicationCreateException
     */
    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = Application::getInstance()->getContainer()->get(
                UserService::class
            );
        }

        return $this->userService;
    }

    /**
     * @return UserRepository
     * @throws ApplicationCreateException
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
    }

    /**
     * @param array $params
     * @return array
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getUserListByParams($params)
    {
        $filter = $params['filter'] ?? [];

        $users = $this->getUserRepository()->findBy(
            $filter,
            ($params['order'] ?? []),
            ($params['limit'] ?? null)
        );

        return $users;
    }

    /**
     * @param string $phone
     * @return User|null
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function searchUserByPhoneNumber(string $phone)
    {
        $user = null;
        $phone = trim($phone);
        if ($phone !== '') {
            // ищем пользователя с таким телефоном в БД сайта
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=PERSONAL_PHONE' => $phone,
                    ]
                ]
            );
            foreach ($items as $item) {
                /** @var User $item */
                if (!$item->isFastOrderUser()) {
                    $user = $item;
                    break;
                }
            }
        }

        if ($this->userServiceMethodsLogEnabled) {
            /** @var LoggerInterface $log */
            $log = method_exists($this, 'log') ? $this->log() : null;
            if ($log) {
                $log->debug(
                    sprintf('Method: %s', __FUNCTION__),
                    [
                        'args' => func_get_args(),
                        'return' => $user,
                    ]
                );
            }
        }

        return $user;
    }

    /**
     * @param string $email
     * @return User|null
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function searchUserByEmail(string $email)
    {
        $user = null;
        $email = trim($email);
        if ($email !== '') {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=EMAIL' => $email,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }

        if ($this->userServiceMethodsLogEnabled) {
            /** @var LoggerInterface $log */
            $log = method_exists($this, 'log') ? $this->log() : null;
            if ($log) {
                $log->debug(
                    sprintf('Method: %s', __FUNCTION__),
                    [
                        'args' => func_get_args(),
                        'return' => $user,
                    ]
                );
            }
        }

        return $user;
    }

    /**
     * @param string $cardNumber
     * @return User|null
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function searchUserByCardNumber(string $cardNumber)
    {
        $user = null;
        $cardNumber = trim($cardNumber);
        if ($cardNumber !== '') {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=UF_DISCOUNT_CARD' => $cardNumber,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }

        if ($this->userServiceMethodsLogEnabled) {
            /** @var LoggerInterface $log */
            $log = method_exists($this, 'log') ? $this->log() : null;
            if ($log) {
                $log->debug(
                    sprintf('Method: %s', __FUNCTION__),
                    [
                        'args' => func_get_args(),
                        'return' => $user,
                    ]
                );
            }
        }

        return $user;
    }

    /**
     * @param int $userId
     * @return User|null
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function searchUserById(int $userId)
    {
        $user = null;
        if ($userId > 0) {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=ID' => $userId,
                    ]
                ]
            );
            $user = reset($items);
        }

        if ($this->userServiceMethodsLogEnabled) {
            /** @var LoggerInterface $log */
            $log = method_exists($this, 'log') ? $this->log() : null;
            if ($log) {
                $log->debug(
                    sprintf('Method: %s', __FUNCTION__),
                    [
                        'args' => func_get_args(),
                        'return' => $user,
                    ]
                );
            }
        }

        return $user;
    }
}
