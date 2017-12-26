<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;
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
    )
    {
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
        $action = $request->get('action', '');
        $phone  = $request->get('phone', '');
        \CBitrixComponent::includeComponentClass('fourpaws:auth.form');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $loginClass = new \FourPawsAuthFormComponent();
        switch ($action) {
            case 'login':
                return $loginClass->ajaxLogin($request->get('login', ''), $request->get('password', ''));
                break;
            case 'resendSms':
                return $loginClass->ajaxResendSms($phone);
                break;
            case 'savePhone':
                return $loginClass->ajaxSavePhone($phone, $request->get('confirmCode', ''));
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
        
        \CBitrixComponent::includeComponentClass('fourpaws:register');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $registerClass = new \FourPawsRegisterComponent();
        
        switch ($action) {
            case 'resendSms':
                return $registerClass->ajaxResendSms($request->get('phone', ''));
                break;
            case 'register':
                return $registerClass->ajaxRegister($request->request->getIterator()->getArrayCopy());
                break;
            case 'savePhone':
                return $registerClass->ajaxSavePhone($request);
                break;
            case 'get':
                return $registerClass->ajaxGet($request);
                break;
        }
        
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/forgotPassword/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function forgotPasswordAction(Request $request) : JsonResponse
    {
        $action = $request->get('action', '');
        
        \CBitrixComponent::includeComponentClass('fourpaws:forgotpassword');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $forgotPasswordClass = new \FourPawsForgotPasswordFormComponent();
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
        
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/changePhone/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function changePhoneAction(Request $request) : JsonResponse
    {
        $action = $request->get('action', '');
        
        \CBitrixComponent::includeComponentClass('fourpaws:personal.profile');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $profileClass = new \FourPawsPersonalCabinetProfileComponent();
        
        switch ($action) {
            case 'confirmPhone':
                return $profileClass->ajaxConfirmPhone($request);
                break;
            case 'resendSms':
                return $profileClass->ajaxResendSms($request->get('phone', ''));
                break;
            case 'get':
                return $profileClass->ajaxGet($request);
                break;
        }
        
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
    
    /**
     * @Route("/changePassword/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     */
    public function changePasswordAction(Request $request) : JsonResponse
    {
        $old_password     = $request->get('old_password', '');
        $password         = $request->get('password', '');
        $confirm_password = $request->get('confirm_password', '');
        
        if (empty($old_password) || empty($password) || empty($confirm_password)) {
            return JsonErrorResponse::create('Должны быть заполнены все поля');
        }
        
        if (\strlen($password) < 6) {
            return JsonErrorResponse::create('Пароль должен содержать минимум 6 символов');
        }
        
        if (!$this->currentUserProvider->getCurrentUser()->equalPassword($old_password)) {
            return JsonErrorResponse::create('Текущий пароль не соответствует введенному');
        }
        
        if ($password !== $confirm_password) {
            return JsonErrorResponse::create('Пароли не соответсвуют');
        }
        
        if ($old_password === $password) {
            return JsonErrorResponse::create('Пароль не может быть таким же, как и текущий');
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->update(
                SerializerBuilder::create()->build()->fromArray(['PASSWORD' => $password], User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
            
            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        }
        
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
    
    /**
     * @Route("/changeData/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     */
    public function changeDataAction(Request $request) : JsonResponse
    {
        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $data           = $request->request->getIterator()->getArrayCopy();
        
        if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
            return JsonErrorResponse::create('Неверный email');
        }
        
        $curUser = $userRepository->findBy(['EMAIL' => $data['EMAIL']], [], 1);
        if ($curUser instanceof User || (\is_array($curUser) && !empty($curUser))) {
            return JsonErrorResponse::create('Такой email уже существует');
        }
        
        /** @var User $user */
        $user = SerializerBuilder::create()->build()->fromArray($data, User::class);
        
        \CBitrixComponent::includeComponentClass('fourpaws:personal.profile');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $profileClass = new \FourPawsPersonalCabinetProfileComponent();
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $userRepository->update(
                $user
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
            
            return JsonSuccessResponse::createWithData(
                'Данные обновлены',
                [
                    'email'    => $user->getEmail(),
                    'fio'      => $user->getFullName(),
                    'gender'   => $user->getGenderText(),
                    'birthday' => $profileClass->replaceRuMonth($user->getBirthday()->format('d #n# Y')),
                ]
            );
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        }
        
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
}
