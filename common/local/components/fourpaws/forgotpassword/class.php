<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsForgotPasswordFormComponent extends \CBitrixComponent
{
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $userAuthService = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
            if ($userAuthService->isAuthorized()) {
                LocalRedirect('/personal/');
            }
            $this->arResult['STEP'] = 'begin';
            
            /** @todo перешли по ссылке из письма для восстановления пароля */
            if (1 === 2) {
                $this->arResult['STEP'] = 'createNewPassword';
            }
            
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }
    
    /**
     * @param $phone
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxSavePassword($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::create($e->getMessage());
            }
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::create(
                    'Ошибка при отправке смс'
                );
            }
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        }
        
        return JsonSuccessResponse::create('Смс отправлено');
    }
    
    /**
     * @param $phone
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::create($e->getMessage());
            }
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::create(
                    'Ошибка при отправке смс'
                );
            }
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        }
        
        return JsonSuccessResponse::create('Смс отправлено');
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxGet($request) : JsonResponse
    {
        $step = $request->get('step', '');
        $mess = '';
        
        $phone = $request->get('phone', '');
        $email = $request->get('email', '');
        if (empty($step)) {
            $recovery = $request->get('recovery', '');
            if ($recovery === 'phone') {
                $step = 'sendSmsCode';
                $res  = $this->ajaxGetSendSmsCode($phone);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                
                $phone = $res;
            } elseif ($recovery === 'email') {
                /** @todo отправка письма для верификации */
                return $this->ajaxGetSendEmailCode($email);
            } else {
                return JsonErrorResponse::create('Не найдено действие для выполнения');
            }
        }
        
        switch ($step) {
            case 'createNewPassword':
                /** @noinspection PhpUnusedLocalVariableInspection */
                $login = !empty($phone) ? $phone : $email;
                break;
        }
        
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/forgotpassword/templates/.default/include/'
                     . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            $mess,
            [
                'html'  => $html,
                'step'  => $step,
                'phone' => $phone ?? '',
            ]
        );
    }
    
    /**
     * @param $phone
     *
     * @return JsonResponse|string
     */
    private function ajaxGetSendSmsCode($phone)
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }
        
        try {
            App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        }
        
        return $phone;
    }
    
    /**
     * @param $email
     *
     * @return JsonResponse
     */
    private function ajaxGetSendEmailCode($email) : JsonResponse
    {
        /** @todo отправка сообщения для верификации по email через expertSender */
        return JsonSuccessResponse::create(
            'На почту ' . $email . ' было отправлено письмо для восстановления пароля'
        );
    }
}
