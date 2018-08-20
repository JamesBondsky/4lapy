<?php
/**
 * Created by PhpStorm.
 * Date: 16.08.2018
 * Time: 19:27
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\UserBundle\Service;


use Bitrix\Main\Mail\Event;
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
        $this->chars = implode('', range('!', '~')); // STRONG
        $this->cUser = new \CUser(); // ничего страшного, просто еще один пустой объект
    }

    /**
     * Метод установит новый пароль для пользователя, независимо от запрета менять пароль, и вышлет письмо с паролем
     *
     * @param int $userId
     *
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws BitrixRuntimeException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function resetPassword(int $userId): void
    {
        /** @var User $user */
        if ($user = $this->userRepository->find($userId)) {

            // Генерируем и меняем пароль
            $policy = \CUser::GetGroupPolicy($user->getGroupsIds());
            do {
                $password = randString($policy['PASSWORD_LENGTH'], $this->chars);
            } while (!empty($this->cUser->CheckPasswordAgainstPolicy($password, $policy)));
            $this->setChangePasswordPossibleForAll(true);
            $this->userRepository->updatePassword($userId, $password);
            $this->setChangePasswordPossibleForAll(false);

            //отправляем письмо СРАЗУ БЕЗ ЗАПИСИ В БАЗУ
            Event::sendImmediate([
                'EVENT_NAME' => 'FRONT_OFFICE_PASSWORD_RESET',
                'LID' => 's1',
                'C_FIELDS' => [
                    'NEW_PASSWORD' => $password,
                    'USER_ID' => $userId,
                    'USER_NAME' => $user->getName(),
                    'USER_LAST_NAME' => $user->getLastName(),
                    'USER_FULL_NAME' => $user->getFullName(),
                    'USER_EMAIL' => $user->getEmail(),
                    'USER_LOGIN' => $user->getLogin(),
                ],
                //'DUPLICATE' => 'N', // по дефолтут тут стоит Y - отправлять копию на специальный адрес для копий
            ]);
        } else {
            throw new NotFoundException('Пользователь "' . $userId . '" не найден.');
        }
    }

    /**
     * @param int $userId
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
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