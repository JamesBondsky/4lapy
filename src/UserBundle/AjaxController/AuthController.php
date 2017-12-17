<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/auth")
 */
class AuthController extends Controller
{
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorization;
    
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        $this->userAuthorization   = $userAuthorization;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    /**
     * @Route("/login/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Exception
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function loginAction(Request $request) : JsonResponse
    {
        $action = $request->request->get('action', '');
        $phone  = $request->get('phone', '');
        \CBitrixComponent::includeComponentClass('fourpaws:auth.form');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $loginClass = new \FourPawsAuthFormComponent();
        switch ($action) {
            case 'login':
                $rawLogin = $request->get('login', '');
                $password = $request->get('password', '');
                
                return $loginClass->ajaxLogin($rawLogin, $password);
                break;
            case 'resendSms':
                return $loginClass->ajaxResendSms($phone);
                break;
            case 'savePhone':
                return $loginClass->ajaxSavePhone($phone);
                break;
            case 'get':
                return $loginClass->ajaxGet($request, $phone);
                break;
        }
        
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/register/", methods={"POST"})
     * @param Request $request
     *
     * @throws \Exception
     * @return null|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerAction(Request $request)
    {
        $action = $request->get('action');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $phone = $request->get('phone');
        
        \CBitrixComponent::includeComponentClass('fourpaws:register');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $registerClass = new \FourPawsRegisterComponent();
        
        switch ($action) {
            case 'resendSms':
                return $registerClass->ajaxResendSms($phone);
                break;
            case 'register':
                return $registerClass->ajaxRegister($request->request->getIterator()->getArrayCopy());
                break;
            case 'savePhone':
                return $registerClass->ajaxSavePhone($request->request->getIterator()->getArrayCopy());
                break;
            case 'get':
                return $registerClass->ajaxGet($request, $phone);
                break;
        }
        
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/forgotPassword/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function forgotPasswordAction(Request $request) : JsonResponse
    {
        $action = $request->get('action', '');
        
        \CBitrixComponent::includeComponentClass('fourpaws:forgotpassword');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $forgotPasswordClass = new \FourPawsForgotPasswordFormComponent();
        switch ($action) {
            case 'savePassword':
                return $forgotPasswordClass->ajaxSavePassword($request->get('phone', ''));
                
                break;
            case 'resendSms':
                return $forgotPasswordClass->ajaxResendSms($request->get('phone', ''));
                
                break;
            case 'get':
                return $forgotPasswordClass->ajaxGet($request);
                break;
        }
        
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/changePassword/", methods={"POST"})
     * @param Request $request
     */
    public function changePasswordAction(Request $request)
    {
        if ($this->userAuthorization->isAuthorized()) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $password = $request->get('password', '');
            /** @noinspection PhpUnusedLocalVariableInspection */
            $confirm = $request->get('confirm', '');
            /** @noinspection PhpUnusedLocalVariableInspection */
            $user = $this->currentUserProvider->getCurrentUser();
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $login = $request->get('login', '');
            /** @noinspection PhpUnusedLocalVariableInspection */
            $checkword = $request->get('checkword', '');
        }
        
        /**
         * todo change password
         */
    }
}
