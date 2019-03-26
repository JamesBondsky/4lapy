<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\Helpers\PhoneHelper;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaSendValidationResponse;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaVerifyResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\External\ExpertsenderService;

class CaptchaService
{
    const SENDER_USER_REGISTRATION = 'user_registration';
    const SENDER_EDIT_INFO = 'edit_info';
    const SENDER_CARD_ACTIVATION = 'card_activation';

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var AppUserService
     */
    private $appUserService;

    /**
     * @var ExpertSenderService
     */
    private $expertSenderService;

    public function __construct(
        UserRepository $userRepository,
        AppUserService $appUserService,
        ExpertSenderService $expertSenderService
    )
    {
        $this->userRepository = $userRepository;
        $this->appUserService = $appUserService;
        $this->expertSenderService = $expertSenderService;
    }

    /**
     * @param string $phoneOrEmail
     * @param string $sender
     * @return CaptchaSendValidationResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendValidation(string $phoneOrEmail, string $sender): CaptchaSendValidationResponse
    {
        $loginType = $this->guessLoginType($phoneOrEmail);

        switch ($loginType) {
            case 'phone':
                if (!in_array($sender, [static::SENDER_USER_REGISTRATION, static::SENDER_EDIT_INFO])) {
                    throw new RuntimeException("Для отправки подтверждения по sms sender может быть либо " . static::SENDER_EDIT_INFO . ", либо " . static::SENDER_USER_REGISTRATION . ". Передан $sender");
                }
                $this->sendValidationBySms($phoneOrEmail, $sender);
                break;
            case 'email':
                if (!in_array($sender, [static::SENDER_EDIT_INFO, static::SENDER_CARD_ACTIVATION])) {
                    throw new RuntimeException("Для отправки подтверждения по почте sender может быть либо " . static::SENDER_EDIT_INFO . ", либо " . static::SENDER_CARD_ACTIVATION . ". Передан $sender");
                }
                $this->sendValidationByEmail($phoneOrEmail, $sender);
                break;
            default:
                throw new RuntimeException("Телефон или email не распознан в параметре $phoneOrEmail");
                break;
        }

        // toDo когда заведут транзакционный шаблон для смены email и настроят механизм смены email на сайте
        $confirmationCodeType = $this->getConfirmationCodeType($loginType, $sender);
        $captchaName = ConfirmCodeService::getCookieName($confirmationCodeType);
        $captchaId = $_COOKIE[$captchaName];
        if (empty($captchaId)) {
            throw new RuntimeException("Не удалось получить проверочную строку из cookie $captchaName");
        }
        return (new CaptchaSendValidationResponse('Код подтверждения успешно отправлен'))
            ->setCaptchaId($captchaId);
    }

    /**
     * @param $login
     * @param $captchaId
     * @param $captchaValue
     * @return CaptchaVerifyResponse
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function verify($login, $captchaId, $captchaValue): CaptchaVerifyResponse
    {
        $loginType = $this->guessLoginType($login);
        $confirmationCodeType = $this->getConfirmationCodeType($loginType, static::SENDER_EDIT_INFO);
        $_COOKIE[ConfirmCodeService::getCookieName($confirmationCodeType)] = $captchaId;
        if (!ConfirmCodeService::checkCode($captchaValue, $confirmationCodeType)) {
            throw new RuntimeException('Некорректный код');
        }
        $text = $loginType == 'phone' ? 'Номер телефона подтвержден' : 'E-mail подтвержден';
        return (new CaptchaVerifyResponse($text))
            ->setCaptchaId($captchaId);
    }

    /**
     * @param string $phone
     * @param string $sender
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    private function sendValidationBySms($phone, $sender)
    {
        $this->checkSender($sender);
        $user = $this->userRepository->findOneByPhone($phone);
        if (
            $sender == static::SENDER_USER_REGISTRATION
            || ($sender == static::SENDER_EDIT_INFO && !$user)
            || ($sender == static::SENDER_CARD_ACTIVATION && $user)
        ) {
            ConfirmCodeService::sendConfirmSms($phone);
        } else {
            throw new RuntimeException("Некорреткные условия отправки проверочного кода в SMS, sender=$sender");
        }
    }

    /**
     * @param string $email
     * @param string $sender
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    private function sendValidationByEmail($email, $sender)
    {
        $this->checkSender($sender);
        $user = $this->appUserService->getCurrentUser();
        if ($sender === static::SENDER_CARD_ACTIVATION) {
            $user->setEmail($email);
            $this->expertSenderService->sendChangeBonusCardFromMobileApp($user);
        } else if ($sender === static::SENDER_EDIT_INFO) {
            $oldUser = $user;
            $curUser = clone $user;
            $curUser->setEmail($email);
            $this->expertSenderService->sendChangeEmailFromMobileApp($oldUser, $curUser);
        } else {
            throw new RuntimeException("Invalid sender: expected card_activation|user_edit, got $sender");
        }
    }

    /**
     * Whether the string is email or phone number
     *
     * @param string $str
     * @return mixed 'email' || 'phone' || false
     */
    private function guessLoginType ( $str )
    {
        if ( preg_match("~^([a-z0-9_\-\.])+@([a-z0-9_\-\.])+\.([a-z0-9])+$~i", $str) ) {
            return 'email';
        }
        elseif ( PhoneHelper::isPhone( $str ) ) {
            return 'phone';
        }

        return false;
    }

    /**
     * По типу сопоставляется значение контрольной строки подтверждения из куки
     * @param string $loginType
     * @param $sender
     * @return string
     */
    private function getConfirmationCodeType(string $loginType, $sender): string
    {
        if (!in_array($loginType, ['phone', 'email'])) {
            throw new RuntimeException("Unexpected loginType = $loginType. Expected phone|email");
        }
        $this->checkSender($sender);

        if ($loginType == 'phone') {
            return 'sms';
        } else {
            switch ($sender) {
                case static::SENDER_USER_REGISTRATION:
                    return 'email_register';
                case static::SENDER_EDIT_INFO:
                    return 'change_email';
                case static::SENDER_CARD_ACTIVATION:
                    return 'email_change_bonus_card';
            }
        }
        // should be unreachable
        throw new RuntimeException("Could not determine sendType. loginType = $loginType sender = $sender");
    }

    /**
     * Проверяет параметр $sender
     * @param $sender
     */
    private function checkSender($sender)
    {
        if (!in_array($sender, [
            static::SENDER_USER_REGISTRATION,
            static::SENDER_EDIT_INFO,
            static::SENDER_CARD_ACTIVATION
        ])) {
            throw new RuntimeException("Unexpected sender = $sender. Expected " . static::SENDER_USER_REGISTRATION . "|" . static::SENDER_EDIT_INFO . "|" . static::SENDER_CARD_ACTIVATION);
        }
    }
}
