<?php

namespace FourPaws\FrontOffice;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\External\Manzana\Model\CardByContractCards;
use FourPaws\External\Manzana\Model\CardValidateResult;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Clients;
use FourPaws\External\ManzanaService;
use FourPaws\FrontOffice\Exception\InvalidArgumentException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use JMS\Serializer\Serializer;

/**
 * Class ManzanaIntegrationService
 *
 * @package FourPaws\FrontOffice
 */
class ManzanaIntegrationService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var ManzanaService $manzanaService */
    private $manzanaService;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;

    /** @var array $validateCardCache */
    private $validateCardCache = [];

    /**
     * ManzanaIntegrationService constructor.
     *
     * @param Serializer $serializer
     * @param ManzanaService $manzanaService
     */
    public function __construct(Serializer $serializer, ManzanaService $manzanaService)
    {
        $this->manzanaService = $manzanaService;
        $this->serializer = $serializer;
    }

    /**
     * @return ManzanaService
     */
    public function getManzanaService()
    {
        return $this->manzanaService;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return UserService
     * @throws ApplicationCreateException
     */
    public function getCurrentUserService()
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(
                CurrentUserProviderInterface::class
            );
        }

        return $this->userCurrentUserService;
    }

    /**
     * @param string $phone
     * @return string
     */
    public function prepareManzanaPhoneNumberValue(string $phone)
    {
        try {
            $phone = PhoneHelper::getManzanaPhone($phone);
        } catch (\Exception $exception) {
            $phone = '';
        }

        return $phone;
    }

    /**
     * @param string $cardNumber
     * @return Result
     */
    public function searchCardByNumber(string $cardNumber)
    {
        $result = new Result();

        if ($cardNumber === '') {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumber')
            );
        }

        $cardRaw = null;
        if ($result->isSuccess()) {
            try {
                /** @var Card $cardRaw */
                $cardRaw = $this->getManzanaService()->searchCardByNumber($cardNumber);
            } catch (CardNotFoundException $exception) {
                $result->addError(
                    new Error('Карта не найдена', 'not_found')
                );
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'searchCardByNumberException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $card = [];
        if ($cardRaw) {
            $card['CONTACT_ID'] = trim($cardRaw->contactId);
            $card['FIRST_NAME'] = trim($cardRaw->firstName);
            $card['SECOND_NAME'] = trim($cardRaw->secondName);
            $card['LAST_NAME'] = trim($cardRaw->lastName);
            $card['BIRTHDAY'] = $cardRaw->birthDate;
            $card['PHONE'] = trim($cardRaw->phone);
            $card['EMAIL'] = trim($cardRaw->email);
            $card['GENDER_CODE'] = (int)$cardRaw->genderCode;
            $card['FAMILY_STATUS_CODE'] = (int)$cardRaw->familyStatusCode;
            $card['HAS_CHILDREN_CODE'] = (int)$cardRaw->hashChildrenCode;
            $card['DEBET'] = (double)$cardRaw->plDebet;
            $card['_IS_BONUS_CARD_'] = $cardRaw->isBonusCard() ? 'Y' : 'N';
            $card['_IS_ACTUAL_CONTACT_'] = $cardRaw->isActualContact() ? 'Y' : 'N';
            $card['_IS_LOAYALTY_PROGRAM_CONTACT_'] = $cardRaw->isLoyaltyProgramContact() ? 'Y' : 'N';
            $card['_IS_ACTIVATED_'] = $card['_IS_ACTUAL_CONTACT_'] === 'Y' && $card['_IS_LOAYALTY_PROGRAM_CONTACT_'] ? 'Y' : 'N';
        }

        $result->setData(
            [
                'card' => $card,
                'cardRaw' => $cardRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $contactId
     * @return Result
     */
    public function getCardsByContactId(string $contactId)
    {
        $result = new Result();

        if ($contactId === '') {
            $result->addError(
                new Error('Не задан id контакта', 'emptyContactId')
            );
        }

        $cardsRaw = [];
        if ($result->isSuccess()) {
            try {
                $cardsRaw = $this->getManzanaService()->getCardsByContactId($contactId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getCardsByContactIdException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $cards = [];
        foreach ($cardsRaw as $cardsItem) {
            /** @var CardByContractCards $cardsItem */
            $cardId = trim($cardsItem->cardId);
            $cards[$cardId] = [
                'ID' => $cardId,
                'TYPE' => trim($cardsItem->bonusType),
                'TYPE_TEXT' => trim($cardsItem->bonusTypeText),
                'NUMBER' => trim($cardsItem->cardNumber),
                'STATUS' => trim($cardsItem->statusText),
                // Активный баланс (pl_active_balance)
                'BALANCE' => (double) $cardsItem->activeBalance,
                // Скидка (pl_discount)
                'DISCOUNT' => (double) $cardsItem->discount,
                // Сумма со скидкой (pl_summdiscounted)
                'SUMM' => (double) $cardsItem->sumDiscounted,
                // Получено баллов (pl_credit)
                'CREDIT' => (double) $cardsItem->credit,
                // Потрачено баллов (pl_debet)
                'DEBET' => (double) $cardsItem->debit,
                '_IS_BONUS_CARD_' => 'N',
                '~RAW~' => $cardsItem,
            ];
            // исправляем тип карты
            // товарищи из манзаны гарантируют: ненулевой pl_debet означает, что карта бонусная
            if ((double)$cardsItem->debit > 0) {
                $cards[$cardId]['_IS_BONUS_CARD_'] = 'Y';
            }
        }

        $result->setData(
            [
                'cards' => $cards,
                'cardsRaw' => $cardsRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $contactId
     * @return CardByContractCards|null
     */
    public function getActualCardByContactId(string $contactId)
    {
        $actualCard = null;
        try {
            $cardsRaw = $this->getManzanaService()->getCardsByContactId($contactId);

            $activeCards = array_filter(
                $cardsRaw,
                function (CardByContractCards $card) {
                    return $card->isActive();
                }
            );

            if (count($activeCards) == 1) {
                $actualCard = reset($activeCards);
            }
        } catch (\Exception $exception) {
            // просто null вернем
        }

        return $actualCard;
    }

    /**
     * @param string $phoneNumber
     * @return Result
     */
    public function getUserDataByPhone(string $phoneNumber)
    {
        $result = new Result();

        $phoneNumber = $this->prepareManzanaPhoneNumberValue($phoneNumber);
        if ($phoneNumber === '') {
            $result->addError(
                new Error('Не задан номер телефона', 'emptyPhoneNumber')
            );
        }

        $clientsRaw = null;
        if ($result->isSuccess()) {
            try {
                /** @var Clients $clients */
                $clientsRaw = $this->getManzanaService()->getUserDataByPhone($phoneNumber);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getUserDataByPhoneException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $clients = [];
        if ($clientsRaw) {
            foreach ($clientsRaw->clients as $client) {
                /** @var Client $client */
                $clients[] = [
                    'CONTACT_ID' => trim($client->contactId),
                    'FIRST_NAME' => trim($client->firstName),
                    'SECOND_NAME' => trim($client->secondName),
                    'LAST_NAME' => trim($client->lastName),
                    'BIRTHDAY' => $client->birthDate,
                    'PHONE' => trim($client->phone),
                    'EMAIL' => trim($client->email),
                    'CARD_NUMBER' => trim($client->cardnumber),
                    'GENDER_CODE' => trim($client->genderCode),
                    '_IS_ACTUAL_CONTACT_' => $client->isActualContact() ? 'Y' : 'N',
                    '_IS_LP_CONTACT_' => $client->isLoyaltyProgramContact() ? 'Y' : 'N',
                    '~RAW~' => $client,
                ];
            }
        }

        $result->setData(
            [
                'clients' => $clients,
                'clientsRaw' => $clientsRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $cardNumber
     * @param bool $getFromCache
     * @return Result
     */
    public function validateCardByNumber(string $cardNumber, bool $getFromCache = false)
    {
        if ($getFromCache && isset($this->validateCardCache[$cardNumber])) {
            return $this->validateCardCache[$cardNumber];
        }

        $result = new Result();

        if ($cardNumber === '') {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumber')
            );
        }

        $validateRaw = null;
        if ($result->isSuccess()) {
            try {
                /** @var CardValidateResult $validateRaw */
                $validateRaw = $this->getManzanaService()->validateCardByNumberRaw($cardNumber);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'validateCardByNumberRawException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $validate = [];
        if ($validateRaw) {
            $validate['CARD_ID'] = trim($validateRaw->cardId);
            $validate['IS_VALID'] = trim($validateRaw->isValid);
            $validate['FIRST_NAME'] = trim($validateRaw->firstName);
            $validate['VALIDATION_RESULT'] = trim($validateRaw->validationResult);
            // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому клиенту
            $validate['VALIDATION_RESULT_CODE'] = (int)$validateRaw->validationResultCode;
            // validationResultCode == 2
            $validate['_IS_CARD_OWNED_'] = $validateRaw->isCardOwned() ? 'Y' : 'N';
            $validate['_IS_CARD_NOT_EXISTS_'] = $validateRaw->isCardNotExists() ? 'Y' : 'N';
        }

        $result->setData(
            [
                'validate' => $validate,
                'validateRaw' => $validateRaw,
            ]
        );

        $this->validateCardCache[$cardNumber] = $result;

        return $result;
    }

    /**
     * @param string $contactId
     * @return Result
     */
    public function getChequesByContactId(string $contactId)
    {
        $result = new Result();

        if ($contactId === '') {
            $result->addError(
                new Error('Не задан id контакта', 'emptyContactId')
            );
        }

        $chequesRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequesRaw = $this->getManzanaService()->getChequesByContactId($contactId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getChequesByContactIdException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $cheques = [];
        if ($chequesRaw) {
            foreach ($chequesRaw as $cheque) {
                if ($cheque->hasItemsBool()) {
                    $cheques[] = [
                        'CHEQUE_ID' => trim($cheque->chequeId),
                        'NUMBER' => trim($cheque->chequeNumber),
                        'DATE' => $cheque->date,
                        'BUSINESS_UNIT_NAME' => trim($cheque->businessUnit),
                        'SUM_DISCOUNTED' => $cheque->sumDiscounted,
                        'PAID_BY_BONUS' => $cheque->paidByBonus,
                        'BONUS' => $cheque->bonus,
                        'SUM' => $cheque->sum,
                    ];
                }
            }
        }

        $result->setData(
            [
                'cheques' => $cheques,
                'chequesRaw' => $chequesRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $cardId
     * @return Result
     */
    public function getChequesByCardId(string $cardId)
    {
        $result = new Result();

        if ($cardId === '') {
            $result->addError(
                new Error('Не задан id карты', 'emptyCardId')
            );
        }

        $chequesRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequesRaw = $this->getManzanaService()->getChequesByCardId($cardId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getChequesByCardIdException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $cheques = [];
        if ($chequesRaw) {
            foreach ($chequesRaw as $cheque) {
                if ($cheque->hasItemsBool()) {
                    $cheques[] = [
                        'CHEQUE_ID' => trim($cheque->chequeId),
                        'NUMBER' => trim($cheque->chequeNumber),
                        'DATE' => $cheque->date,
                        'BUSINESS_UNIT_CODE' => trim($cheque->businessUnitCode),
                        'SUM_DISCOUNTED' => $cheque->sumDiscounted,
                        'PAID_BY_BONUS' => $cheque->paidByBonus,
                        'BONUS' => $cheque->bonus,
                        'SUM' => $cheque->sum,
                    ];
                }
            }
        }

        $result->setData(
            [
                'cheques' => $cheques,
                'chequesRaw' => $chequesRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $chequeId
     * @return Result
     */
    public function getChequeItems(string $chequeId)
    {
        $result = new Result();

        if ($chequeId === '') {
            $result->addError(
                new Error('Не задан id чека', 'emptyChequeId')
            );
        }

        $chequeItemsRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequeItemsRaw = $this->getManzanaService()->getItemsByCheque($chequeId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getItemsByChequeException')
                );

                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $chequeItems = [];
        if ($chequeItemsRaw) {
            foreach ($chequeItemsRaw as $chequeItem) {
                $chequeItems[] = [
                    'CHEQUE_ID' => trim($chequeItem->chequeId),
                    'ARTICLE_NAME' => trim($chequeItem->name),
                    'ARTICLE_NUMBER' => trim($chequeItem->number),
                    'QUANTITY' => (double)$chequeItem->quantity,
                    'PRICE' => (double)$chequeItem->price,
                    'DISCOUNT' => (double)$chequeItem->discount,
                    'SUM' => (double)$chequeItem->sum,
                    'SUM_DISCOUNTED' => (double)$chequeItem->sumDiscounted,
                    'URL' => trim($chequeItem->url),
                    'BONUS' => (double)$chequeItem->bonus,
                ];
            }
        }

        $result->setData(
            [
                'chequeItems' => $chequeItems,
                'chequeItemsRaw' => $chequeItemsRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $phoneNumber
     * @return Client|null
     * @throws InvalidArgumentException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getContactByPhone(string $phoneNumber)
    {
        $contact = null;

        // делаем эту проверку, т.к. на исключение ManzanaServiceContactSearchNullException в getContactByPhone
        // повешено две разнотипных ошибки
        $phoneManzana = $this->prepareManzanaPhoneNumberValue($phoneNumber);
        if ($phoneManzana === '') {
            throw new InvalidArgumentException('Argument phoneNumber is empty');
        }

        try {
            $contact = $this->getManzanaService()->getContactByPhone($phoneManzana);
        } catch (ManzanaServiceContactSearchNullException $exception) {
            // контакта с заданным номером телефона в Манзане нет, но может быть создан
            $this->log()->debug(
                sprintf(
                    '%s exception: %s',
                    __FUNCTION__,
                    $exception->getMessage()
                ),
                [
                    'phoneNumber' => $phoneNumber,
                    'phoneManzana' => $phoneManzana,
                ]
            );
        }

        return $contact;
    }

    /**
     * @param User $user
     * @param array $params
     * @param Client|null $currentContact
     * @return Result
     */
    public function updateContact(User $user, array $params = [], ?Client $currentContact = null)
    {
        $result = new Result();

        $updatedContactId = $currentContact ? $currentContact->contactId : '';
        $userId = (int)$user->getId();
        $cardNumber = $user->getDiscountCardNumber();
        $phoneManzana = $user->getManzanaNormalizePersonalPhone();
        $logContext = [
            'userId' => $userId,
            'phoneManzana' => $phoneManzana,
            'cardNumber' => $cardNumber,
            'existingContactId' => $updatedContactId,
        ];

        $manzanaService = $this->getManzanaService();
        $resultContact = new Client();
        try {
            // заполнение $resultContact по полям $user
            $this->getCurrentUserService()->setClientPersonalDataByCurUser($resultContact, $user);
            if ($updatedContactId !== '') {
                $resultContact->contactId = $updatedContactId;
            }

            if ($cardNumber) {
                $resultContact->cardnumber = $cardNumber;
            }

            // Код места активации карты
            $val = $params['shopOfActivation'] ?? '';
            if ($val !== '') {
                $resultContact->shopOfActivation = $val;
            }
            // Код места регистрации карты (от юзера, заданного в праметрах компонента определяется)
            $val = $params['shopRegistration'] ?? '';
            if ($val !== '') {
                $resultContact->shopRegistration = $val;
            }
            // автоматическая установка флага актуальности контакта
            if ($params['setActualContact']) {
                $resultContact->setActualContact(true);
            }

            // ML: установка карты из карточки юзера в качестве активной карты контакта
            if ($currentContact && $cardNumber) {
                $currentActiveCardId = '';
                /** @var \Doctrine\Common\Collections\ArrayCollection $contactCards */
                $contactCards = $currentContact->cards ?? null;
                if ($contactCards && is_object($contactCards) && !$contactCards->isEmpty()) {
                    foreach ($contactCards as $tmpContactCard) {
                        $tmpCard = $manzanaService->getCardInfo($tmpContactCard->cardNumber, $updatedContactId);
                        if ($tmpCard && $tmpCard->isActive()) {
                            $currentActiveCardId = $tmpCard->cardId;
                            break;
                        }
                    }
                }

                $this->log()->debug(
                    sprintf(
                        '%s currentActiveCardId: %s',
                        __FUNCTION__,
                        $currentActiveCardId
                    ),
                    $logContext
                );

                if ($currentActiveCardId) {
                    $validateResultData = $this->validateCardByNumber($cardNumber, true)->getData();
                    if (isset($validateResultData['validate']['CARD_ID'])) {
                        $newActivateCardId = $validateResultData['validate']['CARD_ID'];
                        $this->log()->debug(
                            sprintf(
                                '%s newActivateCardId: %s',
                                __FUNCTION__,
                                $newActivateCardId
                            ),
                            $logContext
                        );

                        if ($currentActiveCardId !== $newActivateCardId) {
                            $tmpRes = $manzanaService->changeCard($currentActiveCardId, $newActivateCardId);
                            $this->log()->debug(
                                sprintf(
                                    '%s changeCard: %s',
                                    __FUNCTION__,
                                    $tmpRes ? 'success' : 'fail'
                                ),
                                $logContext
                            );
                            if (!$tmpRes) {
                                throw new ContactUpdateException('Не удалось привязать карту');
                            }
                        }
                    }
                }
            }

            $manzanaService->updateContact($resultContact);

            $this->log()->debug(
                sprintf(
                    '%s updateContact: %s',
                    __FUNCTION__,
                    'success'
                ),
                $logContext
            );

        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'manzanaUpdateContactException')
            );
            $this->log()->error(
                sprintf(
                    '%s exception: %s',
                    __FUNCTION__,
                    $exception->getMessage()
                ),
                $logContext
            );
        }

        // сброс тегированного кеша
        $this->clearUserTaggedCache($userId);

        $result->setData(
            [
                'user' => $user,
                'updatedContactId' => $updatedContactId,
                'resultContact' => $resultContact
            ]
        );

        return $result;
    }

    /**
     * Обновление информации в ML, используя для поиска контакта номер телефона из карточки пользователя
     *
     * @param User $user
     * @param array $params
     * @return Result
     */
    public function updateContactByUserPhone(User $user, array $params = [])
    {
        $result = new Result();

        $phoneManzana = $user->getManzanaNormalizePersonalPhone();
        if ($phoneManzana === '') {
            $result->addError(
                new Error(
                    'Не задан телефон для отправки данных в Manzana Loyalty',
                    'manzanaUpdateContactEmptyPhone'
                )
            );
        }

        $updateResult = null;
        if ($result->isSuccess()) {
            try {
                // поиск контакта в Манзане по телефону
                $currentContact = $this->getContactByPhone($phoneManzana);
                // обновление найденного контакта или добавление нового
                $updateResult = $this->updateContact($user, $params, $currentContact);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'updateContactByUserPhoneException')
                );
                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }
        }

        $result->setData(
            [
                'user' => $user,
                'contactId' => $updateResult ? $updateResult->getData()['updatedContactId'] : '',
                'manzanaClient' => $updateResult ? $updateResult->getData()['resultContact'] : null,
            ]
        );

        return $result;
    }

    /**
     * Сброс тегированного кеша, используемого в компонентах сайта
     *
     * @param int $userId
     */
    protected function clearUserTaggedCache(int $userId)
    {
        $clearTags = [];
        $clearTags[] = 'user:'.$userId;
        $clearTags[] = 'personal:bonus:'.$userId;
        TaggedCacheHelper::clearManagedCache($clearTags);
    }
}
