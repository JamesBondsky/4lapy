<?php
/**
 * Created by PhpStorm.
 * Date: 16.08.2018
 * Time: 19:27
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\UserBundle\Service;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\UserBundle\Entity\Group;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;

/**
 * Class UserPasswordService
 * @package FourPaws\UserBundle\Service
 */
class UserPasswordService
{
    /**
     * Массив кодов групп пользователей, пароль которых можно изменить только методом resetPassword
     */
    private const GROUPS_WITH_LOCKED_PASSWORD = ['FRONT_OFFICE_USERS'];
    /**
     * @var bool
     */
    private static $changePasswordPossibleForAll = false;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var string
     */
    private $chars;
    /**
     * @var \CUser
     */
    private $cUser;

    /**
     * UserPasswordService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->chars = implode('', \array_merge(\range(0, 9), \range('a', 'z'), \range('A', 'Z'))); // STRONG
        $this->cUser = new \CUser(); // ничего страшного, просто еще один пустой объект
    }

    /**
     * Метод установит новый пароль для пользователя, независимо от запрета менять пароль, и вышлет письмо с паролем
     *
     * @param int $userId
     *
     * @throws ArgumentTypeException
     * @throws BitrixRuntimeException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function resetPassword(int $userId): void
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);

        if ($user) {
            // Генерируем, меняем отправляем пользователю пароль
            $password = $this->generatePassword($user);
            $this->setChangePasswordPossibleForAll(true);
            $this->userRepository->updatePassword($userId, $password);
            $this->setChangePasswordPossibleForAll(false);
            $this->sendNewPassword($password, $user);
        } else {
            throw new NotFoundException('Пользователь "' . $userId . '" не найден.');
        }
    }

    /**
     *
     * @param User $user
     *
     * @return string
     */
    public function generatePassword(User $user): string
    {
        $policy = \CUser::GetGroupPolicy($user->getGroupsIds());
        do {
            $password = randString($policy['PASSWORD_LENGTH'] + \random_int(1, 3), $this->chars);
        } while (!empty($this->cUser->CheckPasswordAgainstPolicy($password, $policy)));

        return $password;
    }

    /**
     *
     * @param string $password
     * @param User $user
     *
     * @throws ArgumentTypeException
     *
     * @return string
     */
    private function sendNewPassword(string $password, User $user): string
    {
        $result = Event::sendImmediate([
            'EVENT_NAME' => 'FRONT_OFFICE_PASSWORD_RESET',
            'LID' => 's1',
            'C_FIELDS' => [
                'NEW_PASSWORD' => $password,
                'USER_ID' => $user->getId(),
                'USER_NAME' => $user->getName(),
                'USER_LAST_NAME' => $user->getLastName(),
                'USER_FULL_NAME' => $user->getFullName(),
                'USER_EMAIL' => $user->getEmail(),
                'USER_LOGIN' => $user->getLogin(),
            ],
            'DUPLICATE' => 'N',
        ]);
        return $result;
    }

    /**
     * @param int $userId
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return bool
     */
    public function isChangePasswordPossible(int $userId): bool
    {
        $result = true;
        if (!$this->isChangePasswordPossibleForAll() && $user = $this->userRepository->find($userId)) {
            /** @var Group $group */
            foreach ($user->getGroups() as $group) {
                if (\in_array($group->getCode(), self::GROUPS_WITH_LOCKED_PASSWORD, true)) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param bool $changePasswordPossibleForAll
     */
    public function setChangePasswordPossibleForAll(bool $changePasswordPossibleForAll): void
    {
        self::$changePasswordPossibleForAll = $changePasswordPossibleForAll;
    }

    /**
     * @return bool
     */
    public function isChangePasswordPossibleForAll(): bool
    {
        return self::$changePasswordPossibleForAll;
    }

    /**
     * @param string $chars
     *
     * @return UserPasswordService
     */
    public function setChars(string $chars): UserPasswordService
    {
        $this->chars = $chars;
        return $this;
    }

    /**
     * @return string
     */
    public function getChars(): string
    {
        return $this->chars;
    }
}
