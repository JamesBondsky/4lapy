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
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;

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

    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;

    /** @var array $validateCardCache */
    private $validateCardCache = [];

    /**
     * ManzanaIntegrationService constructor.
     *
     * @param ManzanaService $manzanaService
     */
    public function __construct(ManzanaService $manzanaService)
    {
        $this->manzanaService = $manzanaService;
    }

    /**
     * @return ManzanaService
     */
    public function getManzanaService()
    {
        return $this->manzanaService;
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
                new Error('Не задан id чека', 'emptyСhequeId')
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

        $manzanaClient = null;
        $contactId = '';
        $userId = $user->getId();

        if ($result->isSuccess()) {
            $manzanaService = $this->getManzanaService();
            // поиск контакта в Манзане по телефону
            $contact = null;
            try {
                if (!empty($phoneManzana)) {
                    $contact = $manzanaService->getContactByPhone($phoneManzana);
                    $contactId = $contact->contactId;
                }
            } catch (ManzanaServiceContactSearchNullException $exception) {
                // контакта с заданным номером телефона в Манзане нет - будет создан
                $this->log()->debug(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'manzanaContactIdByPhoneException')
                );
                $this->log()->error(
                    sprintf(
                        '%s exception: %s',
                        __FUNCTION__,
                        $exception->getMessage()
                    )
                );
            }

            if ($result->isSuccess()) {
                $cardNumber = $user->getDiscountCardNumber();
                $logContext = [
                    'userId' => $userId,
                    'phoneManzana' => $phoneManzana,
                    'contactId' => $contactId,
                    'cardNumber' => $cardNumber,
                ];

                try {
                    $manzanaClient = new Client();
                    // заполнение $manzanaClient по полям $user
                    $this->getCurrentUserService()->setClientPersonalDataByCurUser($manzanaClient, $user);
                    if ($contactId !== '') {
                        $manzanaClient->contactId = $contactId;
                    }

                    $manzanaClient->cardnumber = $cardNumber;

                    // Код места активации карты
                    $val = $params['shopOfActivation'] ?? '';
                    if ($val !== '') {
                        $manzanaClient->shopOfActivation = $val;
                    }
                    // Код места регистрации карты (от юзера, заданного в праметрах компонента определяется)
                    $val = $params['shopRegistration'] ?? '';
                    if ($val !== '') {
                        $manzanaClient->shopRegistration = $val;
                    }
                    // автоматическая установка флага актуальности контакта
                    if ($params['setActualContact']) {
                        $manzanaClient->setActualContact(true);
                    }

                    // ML: установка введенной карты в качестве активной карты контакта
                    if ($cardNumber) {
                        $currentActiveCardId = '';
                        /** @var \Doctrine\Common\Collections\ArrayCollection $contactCards */
                        $contactCards = $contact->cards;
                        if ($contactCards && is_object($contactCards) && !$contactCards->isEmpty()) {
                            foreach ($contactCards as $tmpContactCard) {
                                $tmpCard = $manzanaService->getCardInfo($tmpContactCard->cardNumber, $contactId);
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

                    $manzanaService->updateContact($manzanaClient);

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
            }
        }

        // сброс тегированного кеша, относящегося к юзеру, используемого в компонентах сайта
        $clearTags = [];
        $clearTags[] = 'personal:bonus:'.$userId;
        if ($clearTags) {
            TaggedCacheHelper::clearManagedCache($clearTags);
        }

        $result->setData(
            [
                'user' => $user,
                'contactId' => $contactId,
                'manzanaClient' => $manzanaClient
            ]
        );

        return $result;
    }
}
