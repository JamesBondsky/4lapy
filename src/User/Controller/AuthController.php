<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\User\Exceptions\ChangePasswordException;
use FourPaws\User\Exceptions\RegisterException;
use FourPaws\User\Exceptions\TooManyUserFoundException;
use FourPaws\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    /**@var UserService */
    protected $userService;
    
    public function __construct()
    {
        $this->userService = Application::getInstance()->getContainer()->get('user.service');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginAction(Request $request) : JsonResponse
    {
        $rawLogin = $request->request->get('login') ?? '';
        $password = $request->request->get('password') ?? '';
        
        try {
            $this->userService->login($rawLogin, $password);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::create('Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        }
        
        return JsonSuccessResponse::create('Вы успешно авторизованы.');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request) : JsonResponse
    {
        $rawLogin = $request->request->get('login') ?? '';
        
        try {
            $this->userService->restorePassword($rawLogin);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::create('Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        }
        
        return JsonSuccessResponse::create('Инструкция по восстановлению пароля успешно отправлена.');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(Request $request) : JsonResponse
    {
        $data = [];
        
        try {
            $this->userService->register($data);
        } catch (RegisterException $e) {
        
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Системная ошибка при попытке регистрации. Пожалуйста, обратитесь к администратору сайта.');
        }
        
        return JsonSuccessResponse::create('Вы успешно Зарегистрированы.');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request) : JsonResponse
    {
        $password = $request->request->get('password') ?? '';
        $confirm  = $request->request->get('confirm') ?? '';
        
        $isAuthorized = $this->userService->isAuthorized();
        
        try {
            if ($isAuthorized) {
                $user = $this->userService->getCurrentUser();
                
                $login     = $user->getLogin();
                $checkword = $user->getCheckword();
            } else {
                $login     = $request->request->get('login') ?? '';
                $checkword = $request->request->get('checkword') ?? '';
            }
            
            $result = $this->userService->changePassword($login, $checkword, $password, $confirm);
            
            if (!$result->isSuccess()) {
                throw new ChangePasswordException(implode($result->getErrorMessages()));
            }
        } catch (ChangePasswordException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Системная ошибка при попытке изменении пароля. Пожалуйста, обратитесь к администратору сайта.');
        }
        
        return JsonSuccessResponse::create('Пароль успешно изменен');
    }
    
}
