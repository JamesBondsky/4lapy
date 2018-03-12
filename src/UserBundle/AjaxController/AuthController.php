<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/auth")
 */
class AuthController extends Controller
{
    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct(
        AjaxMess $ajaxMess
    ) {
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/login/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginAction(Request $request): JsonResponse
    {
        $action = $request->get('action', '');
        \CBitrixComponent::includeComponentClass('fourpaws:auth.form');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $loginClass = new \FourPawsAuthFormComponent();
        } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        switch ($action) {
            case 'login':
                return $loginClass->ajaxLogin($request->get('login', ''), $request->get('password', ''),
                    $request->get('backurl', ''));
                break;
            case 'resendSms':
                return $loginClass->ajaxResendSms($request->get('phone', ''));
                break;
            case 'savePhone':
                return $loginClass->ajaxSavePhone($request->get('phone', ''), $request->get('confirmCode', ''));
                break;
            case 'unionBasket':
                return $loginClass->ajaxUnionBasket($request);
                break;
            case 'get':
                return $loginClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/register/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(Request $request): JsonResponse
    {
        $action = $request->get('action');

        \CBitrixComponent::includeComponentClass('fourpaws:register');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $registerClass = new \FourPawsRegisterComponent();
        } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        switch ($action) {
            case 'resendSms':
                return $registerClass->ajaxResendSms($request->get('phone', ''));
                break;
            case 'register':
                return $registerClass->ajaxRegister($request->request->all());
                break;
            case 'savePhone':
                return $registerClass->ajaxSavePhone($request);
                break;
            case 'get':
                return $registerClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/forgotPassword/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request): JsonResponse
    {
        $action = $request->get('action', '');

        \CBitrixComponent::includeComponentClass('fourpaws:forgotpassword');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $forgotPasswordClass = new \FourPawsForgotPasswordFormComponent();
        } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        switch ($action) {
            case 'savePassword':
                return $forgotPasswordClass->ajaxSavePassword($request);

                break;
            case 'resendSms':
                return $forgotPasswordClass->ajaxResendSms($request->get('phone', ''));

                break;
            case 'get':
                return $forgotPasswordClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }
}
