<?php

namespace FourPaws\FrontOffice\Bitrix\Component;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\Traits\ManzanaIntegrationServiceTrait;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;


abstract class CustomerRegistration extends SubmitForm
{
    use ManzanaIntegrationServiceTrait;

    const EXTERNAL_GENDER_CODE_M = 1;
    const EXTERNAL_GENDER_CODE_F = 2;
    const BITRIX_GENDER_CODE_M = 'M';
    const BITRIX_GENDER_CODE_F = 'F';

    /**
     * @param string $externalGenderCode
     * @return string
     */
    public function getBitrixGenderByExternalGender(string $externalGenderCode)
    {
        $result = '';
        $externalGenderCode = (int)$externalGenderCode;
        if ($externalGenderCode === static::EXTERNAL_GENDER_CODE_M) {
            $result = static::BITRIX_GENDER_CODE_M;
        } elseif ($externalGenderCode === static::EXTERNAL_GENDER_CODE_F) {
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
                    //$registeredUserGroupId = \CGroup::GetIDByCode('REGISTERED_USERS');
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

        // сброс тегированного кеша, используемого в компонентах сайта, относящегося к юзеру
        $clearTags = [];
        $clearTags[] = 'user:'.$userId;
        if ($clearTags) {
            TaggedCacheHelper::clearManagedCache($clearTags);
        }

        $result->setData(
            [
                'userId' => $userId,
                'fields' => $fields,
            ]
        );

        return $result;
    }
}
