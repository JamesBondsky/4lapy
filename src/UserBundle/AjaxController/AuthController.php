<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
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
    ) {
        $this->userAuthorization = $userAuthorization;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    /**
     * @Route("/login/", methods={"POST"})
     */
    public function loginAction(Request $request)
    {
        $rawLogin = $request->request->get('login', '');
        $password = $request->request->get('password', '');
        
        try {
            $this->userAuthorization->login($rawLogin, $password);
        } catch (UsernameNotFoundException $exception) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        } catch (InvalidCredentialException $credentialException) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        } catch (\Exception $exception) {
            return JsonErrorResponse::create(
                'Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.'
            );
        }
        
        return JsonSuccessResponse::create('Вы успешно авторизованы.');
    }
    
    /**
     * @Route("/register/", methods={"POST"})
     * @param Request $request
     */
    public function registerAction(Request $request)
    {
        /**
         * todo обработка формы или DTO сериализации с валидаторами
         */
    }
    
    /**
     * @Route("/forgotPassword/", methods={"POST"})
     * @param Request $request
     */
    public function forgotPasswordAction(Request $request)
    {
        /**
         * todo restore
         */
    }
    
    /**
     * @Route("/changePassword/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function changePasswordAction(Request $request) : JsonResponse
    {
        if ($this->userAuthorization->isAuthorized()) {
            $password = $request->get('password', '');
            $confirm  = $request->get('confirm_password', '');
            $old      = $request->get('old_password', '');
            
            if (empty($password) || empty($confirm) || empty($old)) {
                return JsonErrorResponse::create('Необходимо заполнить все поля');
            }
            if(\strlen($password) < 6){
                $password = 'Пароль должен содержать минимум 6 символов';
            }
            if ($old === $password) {
                return JsonErrorResponse::create('Пароль не может быть такой же как текущий');
            }
            if ($this->currentUserProvider->getCurrentUser()->equalPassword($old)) {
                return JsonErrorResponse::create('Введенный вами пароль не совпадает с текущим');
            }
            if ($confirm !== $password) {
                return JsonErrorResponse::create('Пароли не совпадают');
            }
        } else {
            return JsonErrorResponse::create('Вы не авторизованы');
        }
        
        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        try {
            $res = $userRepository->update(
                SerializerBuilder::create()->build()->fromArray(['PASSWORD' => $password], User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
            
            return JsonSuccessResponse::create('Ваш пароль успешно изменен');
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
     */
    public function changePersonalDataAction(Request $request) : JsonResponse
    {
        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $data = $request->request->getIterator()->getArrayCopy();
        if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false)
        {
            return JsonErrorResponse::create('Неверный email');
        }
        try {
            $res  = $userRepository->update(
                SerializerBuilder::create()->build()->fromArray($data, User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
        
            return JsonSuccessResponse::create('Ваш профиль успешно изменен');
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        }
    
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
    
    /**
     * @Route("/changePhone/", methods={"POST"})
     * @param Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function changePhoneAction(Request $request) : JsonResponse
    {
        $phone = $request->get('phone', '');
        $action = $request->get('action', '');
        switch($action){
            case 'save':
                break;
            case 'resendSms':
                break;
            case 'get':
                $step = $request->get('step', '');
                switch($step){
                    case 'confirm':
                        try {
                            $phone = PhoneHelper::normalizePhone($phone);
                        } catch (WrongPhoneNumberException $e) {
                            return JsonSuccessResponse::create('Неверный формат номера телефона');
                        }
                        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
                        $userRepository = $this->currentUserProvider->getUserRepository();
                        try {
                            $res = $userRepository->update(
                                SerializerBuilder::create()->build()->fromArray(['PERSONAL_PHONE' => $phone], User::class)
                            );
                            if (!$res) {
                                return JsonErrorResponse::create('Произошла ошибка при обновлении');
                            }
                        
                            return JsonSuccessResponse::createWithData('Ваш телефон успешно изменен', ['phone' => $phone, 'step' => $step, 'html' => $html]);
                        } catch (BitrixRuntimeException $e) {
                            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
                        } catch (ConstraintDefinitionException $e) {
                        }
                        break;
                    case 'phone':
                        break;
                }
                break;
        }
    
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
}
