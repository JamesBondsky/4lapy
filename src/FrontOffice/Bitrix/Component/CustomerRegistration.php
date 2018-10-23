<?php

namespace FourPaws\FrontOffice\Bitrix\Component;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\Traits\ManzanaIntegrationServiceTrait;
use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;


abstract class CustomerRegistration extends SubmitForm
{
    use ManzanaIntegrationServiceTrait;

    public const EXTERNAL_GENDER_CODE_M = 1;
    public const EXTERNAL_GENDER_CODE_F = 2;
    public const BITRIX_GENDER_CODE_M = 'M';
    public const BITRIX_GENDER_CODE_F = 'F';

    /**
     * @param string $externalGenderCode
     * @return string
     */
    public function getBitrixGenderByExternalGender(string $externalGenderCode): string
    {
        $result = '';
        $externalGenderCodeInt = (int)$externalGenderCode;
        if ($externalGenderCodeInt === static::EXTERNAL_GENDER_CODE_M) {
            $result = static::BITRIX_GENDER_CODE_M;
        } elseif ($externalGenderCodeInt === static::EXTERNAL_GENDER_CODE_F) {
            $result = static::BITRIX_GENDER_CODE_F;
        }

        return $result;
    }

    /**
     * @param string $cardNumber
     * @param bool $getFromCache
     * @return Result
     * @throws ApplicationCreateException
     */
    public function validateCardByNumber(string $cardNumber, bool $getFromCache = false)
    {
        $result = $this->getManzanaIntegrationService()->validateCardByNumber(
            $cardNumber,
            $getFromCache
        );

        return $result;
    }

    /**
     * @param string $phoneNumber
     * @return Result
     * @throws ApplicationCreateException
     */
    public function getUserDataByPhone(string $phoneNumber)
    {
        $result = $this->getManzanaIntegrationService()->getUserDataByPhone(
            $phoneNumber
        );

        return $result;
    }

    /**
     * @param string $cardNumber
     * @return Result
     * @throws ApplicationCreateException
     */
    public function searchCardByNumber(string $cardNumber)
    {
        $result = $this->getManzanaIntegrationService()->searchCardByNumber(
            $cardNumber
        );

        return $result;
    }

    /**
     * @param string $contactId
     * @return Result
     * @throws ApplicationCreateException
     */
    public function getCardsByContactId(string $contactId)
    {
        $result = $this->getManzanaIntegrationService()->getCardsByContactId(
            $contactId
        );

        return $result;
    }

    /**
     * @param string $contactId
     * @return Result
     * @throws ApplicationCreateException
     */
    public function getChequesByContactId(string $contactId)
    {
        $result = $this->getManzanaIntegrationService()->getChequesByContactId(
            $contactId
        );

        return $result;
    }

    /**
     * @param string $cardId
     * @return Result
     * @throws ApplicationCreateException
     */
    public function getChequesByCardId(string $cardId)
    {
        $result = $this->getManzanaIntegrationService()->getChequesByCardId(
            $cardId
        );

        return $result;
    }

    /**
     * @param string $chequeId
     * @return Result
     * @throws ApplicationCreateException
     */
    public function getChequeItems(string $chequeId)
    {
        $result = $this->getManzanaIntegrationService()->getChequeItems(
            $chequeId
        );

        return $result;
    }

    /**
     * @param User $user
     * @return Result
     */
    protected function createUser(User $user)
    {
        $result = new Result();

        try {
            $createResult = $this->getUserRepository()->create($user);
            if ($createResult) {
                if ($user->getId()) {
                    // привязка пользователя к группе "Зарегистрированные пользователи"
                    $registeredUserGroupId = $this->getGroupIdByCode('REGISTERED_USERS');
                    if ($registeredUserGroupId) {
                        \CUser::AppendUserGroup($user->getId(), [$registeredUserGroupId]);
                    }
                }
            } else {
                $result->addError(
                    new Error('Нераспознанная ошибка', 'createUserUnknownError')
                );
            }
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'createUserException')
            );

            $this->log()->error(
                sprintf(
                    '%s exception: %s',
                    __FUNCTION__,
                    $exception->getMessage()
                )
            );
        }

        $result->setData(
            [
                'user' => $user,
            ]
        );

        return $result;
    }

    /**
     * @param int $userId
     * @param array $fields
     * @return Result
     */
    protected function updateUser(int $userId, array $fields)
    {
        $result = new Result();

        if ($userId <= 0) {
            $result->addError(
                new Error('Не задан id пользователя, либо задан некорректно', 'updateUserIncorrectUserId')
            );
        }
        if ($result->isSuccess()) {
            if (isset($fields['ID'])) {
                unset($fields['ID']);
            }

            try {
                $updateResult = $this->getUserRepository()->updateData($userId, $fields);
                if (!$updateResult) {
                    $result->addError(new Error('Нераспознанная ошибка', 'updateUserUnknownError'));
                }
            } catch (\Exception $exception) {
                $result->addError(new Error($exception->getMessage(), 'updateUserException'));

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        // сброс тегированного кеша
        $this->clearUserTaggedCache($userId);

        $result->setData(
            [
                'userId' => $userId,
                'fields' => $fields,
            ]
        );

        return $result;
    }

    /**
     * @return Serializer
     * @throws ApplicationCreateException
     */
    protected function getSerializer()
    {
        return $this->getManzanaIntegrationService()->getSerializer();
    }

    /**
     * @param User $user
     * @return array
     * @throws ApplicationCreateException
     */
    protected function convertUserToArray(User $user)
    {
        return $this->getSerializer()->toArray($user, SerializationContext::create()->setGroups(['update']));
    }

    /**
     * @param array $fields
     * @return User
     * @throws ApplicationCreateException
     */
    protected function convertUserFromArray(array $fields)
    {
        return $this->getSerializer()->fromArray($fields, DeserializationContext::create()->setGroups(['update']));
    }
}
