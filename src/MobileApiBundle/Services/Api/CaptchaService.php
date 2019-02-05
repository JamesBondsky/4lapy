<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\SmsService;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaSendValidationResponse;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaVerifyResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\BitrixCaptchaService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserService as AppUserService;

class CaptchaService
{
    /**
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var AppUserService
     */
    private $appUserService;

    /**
     * @var BitrixCaptchaService
     */
    private $bitrixCaptchaService;

    /**
     * @var SmsService
     */
    private $smsService;


    public function __construct(
        UserRepository $userRepository,
        AppUserService $appUserService,
        SmsService $smsService
    )
    {
        $this->smsService = $smsService;
        $this->userRepository = $userRepository;
        $this->appUserService = $appUserService;
    }

    /**
     * @param string $phoneOrEmail
     * @param string $sender
     * @return CaptchaSendValidationResponse
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    public function sendValidation(string $phoneOrEmail, string $sender): CaptchaSendValidationResponse
    {
        $loginType = $this->guessLoginType($phoneOrEmail);

        if ($loginType) {
            if ($loginType == 'phone') {
                $this->sendValidationInSms($phoneOrEmail, $sender);
            } elseif (in_array($sender, ['edit_info', 'card_activation'])) {
                $this->sendValidationInEmail($phoneOrEmail, $sender);
            }
            return (new CaptchaSendValidationResponse('Код подтверждения успешно отправлен'))
                ->setCaptchaId($this->bitrixCaptchaService->getId());
        }
    }

    /**
     * @param $login
     * @param $captchaId
     * @param $captchaValue
     * @return CaptchaVerifyResponse
     */
    public function verify($login, $captchaId, $captchaValue): CaptchaVerifyResponse
    {
        $loginType = $this->guessLoginType($login);
        if ($GLOBALS['APPLICATION']->CaptchaCheckCode($captchaValue, $captchaId)) {
            $this->bitrixCaptchaService = new BitrixCaptchaService();

            $captchaId = "{$this->bitrixCaptchaService->getCode()}:{$this->bitrixCaptchaService->getId()}";

            if ($loginType == 'phone') {
                $text = 'Номер телефона подтвержден';
            } else {
                $text = 'E-mail подтвержден';
            }
            return (new CaptchaVerifyResponse($text))
                ->setCaptchaId($captchaId);
        } else {
            throw new RuntimeException('Некорректный код');
        }
    }

    /**
     * @param string $phone
     * @param string $sender
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    private function sendValidationInSms($phone, $sender)
    {
        $user = $this->userRepository->findOneByPhone($phone);
        if (
            $sender == 'user_registration'
            || ($sender == 'edit_info' && !$user)
            || ($sender == 'card_activation' && $user)
        ) {
            $this->bitrixCaptchaService = new BitrixCaptchaService();
            $verificationCode = $this->bitrixCaptchaService->getCode();
            $this->smsService->sendSmsImmediate('Код подтверждения: ' . $verificationCode, $phone);
            if ($user) {
                $this->saveUserVerificationCode(current($user)->getId(), $verificationCode);
            }
        } else {
            throw new RuntimeException('Некорреткные условия для страницы с капчей');
        }
    }

    /**
     * @param string $email
     * @param string $sender
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    private function sendValidationInEmail($email, $sender)
    {
        if (!$user = $this->appUserService->findOneByEmail($email)) {
            throw new RuntimeException("Пользователь с email $email не найден в базе данных");
        }
        $currentUserId = $this->appUserService->getCurrentUserId();
        $userId = $user->getId();

        if (in_array($sender, ['card_activation', 'edit_info']) && $userId != $currentUserId) {
            throw new RuntimeException("Пользователь авторизован c ID $currentUserId, а переданный email $email зарегестрирован у пользователя с ID $userId");
        }

        $this->bitrixCaptchaService = new BitrixCaptchaService();
        $verificationCode = $this->bitrixCaptchaService->getCode();
        $sendResult = \Bitrix\Main\Mail\Event::sendImmediate(array(
            'EVENT_NAME' => 'SEND_VER_CODE_APP',
            'LID' => \Bitrix\Main\Application::getInstance()->getContext()->getSite(),
            'DUPLICATE' => 'N',
            'C_FIELDS' => [
                'EMAIL_TO' => $email,
                'VER_CODE' => $verificationCode
            ],
        ));
        $sendResult = ($sendResult === \Bitrix\Main\Mail\Event::SEND_RESULT_SUCCESS);
        if (!$sendResult) {
            throw new RuntimeException('Ошибка отправки кода верификации');
        }
        $this->saveUserVerificationCode($userId, $verificationCode);
    }

    /**
     * @param $userId
     * @param $verificationCode
     */
    private function saveUserVerificationCode($userId, $verificationCode)
    {
        (new \CUser)->Update($userId, ['CONFIRM_CODE' => $verificationCode]);
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
        elseif ( $this->isPhoneNumber( $str ) ) {
            return 'phone';
        }

        return false;
    }

    /**
     * @param string $phone
     * @return bool
     */
    private function isPhoneNumber( $phone )
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phoneLength = strlen( $phone );
        $firstChar = substr( $phone, 0, 1 );
        $secondChar = substr( $phone, 1, 1 );

        return (
            ($phoneLength == 10 && $firstChar == 9)
            || ($phoneLength == 11 && in_array($firstChar, [7,8]) && $secondChar == 9)
        );
    }
}
