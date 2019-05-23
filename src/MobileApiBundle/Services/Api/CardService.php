<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\UserTable;
use FourPaws\External\Manzana\Enum\Card;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Object\ChangeCardProfile;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Exception\CardAlreadyUsedException;
use FourPaws\MobileApiBundle\Exception\EmailAlreadyUsed;
use FourPaws\MobileApiBundle\Exception\InvalidCardException;
use FourPaws\MobileApiBundle\Exception\InvalidCredentialException;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Exception\CardNotValidException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\PersonalBundle\Entity\UserBonus as AppUserBonus;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;


class CardService
{
    /**
     * @var AppManzanaService
     */
    private $appManzanaService;

    /**
     * @var AppUserService
     */
    private $appUserService;

    /**
     * @var AppUserBonus
     */
    private $appUserBonus;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ApiUserService
     */
    private $apiUserService;

    /**
     * @var ApiCaptchaService
     */
    private $apiCaptchaService;

    public function __construct(
        AppManzanaService $appManzanaService,
        AppUserService $appUserService,
        AppUserBonus $appUserBonus,
        UserRepository $userRepository,
        ApiUserService $apiUserService,
        ApiCaptchaService $apiCaptchaService
    )
    {
        $this->appManzanaService = $appManzanaService;
        $this->appUserService = $appUserService;
        $this->appUserBonus = $appUserBonus;
        $this->userRepository = $userRepository;
        $this->apiUserService = $apiUserService;
        $this->apiCaptchaService = $apiCaptchaService;
    }

    /**
     * @param int $cardNumber
     *
     * @return bool
     */
    public function isActive($cardNumber): bool
    {
        $activated = UserTable::query()
                ->addFilter('UF_DISCOUNT_CARD', $cardNumber)
                ->exec()
                ->getSelectedRowsCount() > 0;
        return $activated;
    }

    /**
     * Задача: зная номер карты, получить по ней данные из МЛ и вернуть их в МП
     * Обязательно сделать проверку на совпадение номера телефона (phone)
     *
     * @param $cardNumber
     * @return ChangeCardProfile
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     * @throws \Exception
     */
    public function getCardDataFromManzana($cardNumber): ChangeCardProfile
    {
        $user = $this->appUserService->getCurrentUser();

        $isCardNotLinked = false;
        try {
            $card = $this->appManzanaService->searchCardByNumber($cardNumber);
            if (!$card->contactId) {
                throw new CardNotFoundException(); // На самом деле найдена, но для совместимости со старым кодом кидаем прежнее исключение
            }

            $cardInfo = $this->appManzanaService->getCardInfo(
                $cardNumber,
                $card->contactId
            );

            if (!$cardInfo || !\in_array($cardInfo->status, [Card::STATUS_NEW, Card::STATUS_ACTIVE], true)) {
                throw new CardNotValidException('Замена невозможна. Обратитесь на Горячую Линию.');
            }

            $userPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $user->getPersonalPhone());
            $cardPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $card->phone);

            if (!empty($cardPhone) && $userPhone != $cardPhone) {
                throw new \Exception("Не удалось получить данные по бонусной карте: номер телефона авторизованного пользователя $userPhone и номер телефона карты $cardPhone не совпадают.");
            }
        } catch (CardNotFoundException $e) {
            // если не найдена - значит, еще не привязана ни к одному клиенту
            $isCardNotLinked = true;
        }

        if ($isCardNotLinked)
        {
            $userPhone = $user->getPersonalPhone();
            $lastName = $user->getLastName();
            $firstName = $user->getName();
            $secondName = $user->getSecondName();
            $birthDateBase = $user->getBirthday();
            $email = $user->getEmail();
        } else {
            $lastName = $card->lastName;
            $firstName = $card->firstName;
            $secondName = $card->secondName;
            $birthDateBase = $card->birthDate;
            $email = $card->email;
        }

        $cardProfile = (new ChangeCardProfile())
            ->setNewCardNumber($cardNumber)
            ->setLastName($lastName ?? '')
            ->setFirstName($firstName ?? '')
            ->setPhone($userPhone);

        if ($birthDateBase) {
            $birthDate = (new \DateTime())->setTimestamp($birthDateBase->getTimestamp());
            $cardProfile->setBirthDate($birthDate);
        }

        if (stristr($email, '@register.phone') === false) {
            $cardProfile->setEmail($email ?? '');
        }

        if ($secondName) {
            $cardProfile->setSecondName($secondName);
        }

        return $cardProfile;
    }

    /**
     * @param string $email
     * @return Response\CaptchaSendValidationResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendConfirmationToEmail(string $email)
    {
        try {
            if ($user = $this->appUserService->findOneByEmail($email)) {
                if ($user->isEmailConfirmed() && $this->apiUserService->getCurrentApiUser()->getId() !== $user->getId()) {
                    throw new EmailAlreadyUsed();
                }
            }
        } catch (NotFoundException $e) {
            $this->apiUserService->update((new User())->setEmail($email));
        }
        return $this->apiCaptchaService->sendValidation($email, 'card_activation');
    }

    /**
     * Задача: проверить капчу, и если все гут - апдейтить юзера в БД
     * @param ChangeCardProfile $cardProfile
     * @param $captchaId
     * @param $captchaValue
     * @return void
     * @throws CardNotValidException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchNullException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function changeCardConfirmPin(ChangeCardProfile $cardProfile, $captchaId, $captchaValue)
    {
        $user = $this->appUserService->getCurrentUser();
        $oldCard = $user->getDiscountCardNumber();
        $newCard = $cardProfile->getNewCardNumber();

        // 1. проверяем капчу
        $confirmationCodeType = 'email_change_bonus_card';
        $_COOKIE[ConfirmCodeService::getCookieName($confirmationCodeType)] = $captchaId;
        if (!ConfirmCodeService::checkCode($captchaValue, $confirmationCodeType)) {
             throw new InvalidCredentialException();
        }

        // 2. проверяем, нет ли уже в базе такого номера карты
        if ($this->isActive($cardProfile->getNewCardNumber())) {
            throw new CardAlreadyUsedException();
        }

        // 3. заменяем карту в манзане
        // 3.1 получаем ID старой карты
        $newCardValidResult = $this->appManzanaService->validateCardByNumberRaw($newCard);
        if (!$newCardValidResult->cardId) {
            throw new InvalidCardException();
        }
        $newCardId = $newCardValidResult->cardId;

        // 3.2 получаем ID новой карты
        $client = $this->appManzanaService->getContactByUser($user);
        $card = $this->appManzanaService->getCardInfo($oldCard, $client->contactId);
        if ($card === null) {
            throw new InvalidCardException();
        }
        $oldCardId = $card->cardId;

        // 3.3 производим замену
        $manzanaChangeCardResult = $this->appManzanaService->changeCard($oldCardId, $newCardId);

        // 4. заменяем данные в битриксовом профиле пользователя
        if ($manzanaChangeCardResult) {
            $user->setDiscountCardNumber($newCard);
            $this->userRepository->update($user);
        }
    }
}
