<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\SmsService;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\BitrixCaptchaService;
use FourPaws\UserBundle\Service\UserService as UserBundleService;

class CaptchaService
{
    /**
     * @var UserBundleService
     */
    private $userBundleService;

    /**
     * @var BitrixCaptchaService
     */
    private $bitrixCaptchaService;

    public function __construct(UserBundleService $userBundleService)
    {
        $this->userBundleService = $userBundleService;
    }

    /**
     * @param string $login
     * @param string $sender
     * @return array
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    public function sendValidation($login, $sender)
    {
        $loginType = $this->guessLoginType($login);

        if ($loginType) {
            if ($loginType == 'phone') {
                $this->sendValidationInSms($login, $sender);
            } elseif (in_array($sender, ['edit_info', 'card_activation'])) {
                $this->sendValidationInEmail($login, $sender);
            }
            return [
                'captcha_id' => $this->bitrixCaptchaService->getId(),
                'feedback_text' => 'Код подтверждения успешно отправлен',
            ];
        }
    }

    /**
     * @param $login
     * @param $captchaId
     * @param $captchaValue
     * @return array
     */
    public function verify($login, $captchaId, $captchaValue)
    {
        $loginType = $this->guessLoginType($login);
        if ($GLOBALS['APPLICATION']->CaptchaCheckCode($captchaValue, $captchaId)) {
            $this->bitrixCaptchaService = new BitrixCaptchaService();

            $result = [
                'captcha_id' => "{$this->bitrixCaptchaService->getCode()}:{$this->bitrixCaptchaService->getId()}"
            ];

            if ($loginType == 'phone') {
                $result['feedback_text'] = 'Номер телефона подтвержден';
            } else {
                $result['feedback_text'] = 'E-mail подтвержден';
            }
        } else {
            throw new RuntimeException('Некорректный код');
        }


        return $result;
    }

    /**
     * @param string $phone
     * @param string $sender
     * @throws ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    private function sendValidationInSms($phone, $sender)
    {
        $user = $this->userBundleService->findOneByPhone($phone);
        if (
            $sender == 'user_registration'
            || ($sender == 'edit_info' && !$user)
            || ($sender == 'card_activation' && $user)
        ) {
            $this->bitrixCaptchaService = new BitrixCaptchaService();
            $verificationCode = $this->bitrixCaptchaService->getCode();
            (new SmsService())->sendSmsImmediate('Код подтверждения: ' . $verificationCode, $phone);
            $this->saveUserVerificationCode($user->getId(), $verificationCode);
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
        $user = $this->userBundleService->findOneByEmail($email);

        if ($sender == 'edit_info' && $user) {
            throw new RuntimeException('Этот email уже был использован для отправки капчи');
        } elseif ($sender == 'card_activation' && $user && $user['ID'] != $this->userBundleService->getCurrentUserId()) {
            throw new RuntimeException('Этот email уже был использован для отправки капчи');
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
        $this->saveUserVerificationCode($user->getId(), $verificationCode);
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
