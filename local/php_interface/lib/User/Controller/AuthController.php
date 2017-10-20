<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Model\ResponseContent\JsonContent;
use FourPaws\User\Exceptions\ChangePasswordException;
use FourPaws\User\Exceptions\RegisterException;
use FourPaws\User\Exceptions\TooManyUserFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    /**@var \FourPaws\User\UserService */
    protected $_userService;
    
    public function __construct()
    {
        $this->_userService = Application::getInstance()->getContainer()->get('user.service');
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function loginAction(Request $request) : JsonResponse
    {
        $rawLogin = $request->request->get('login') ?? '';
        $password = $request->request->get('password') ?? '';
        
        try {
            $this->_userService->login($rawLogin, $password);
        } catch (TooManyUserFoundException $e) {
            return JsonResponse::create(new JsonContent('Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.',
                                                        false));
        } catch (\Exception $e) {
            return JsonResponse::create(new JsonContent('Неверный логин или пароль.', false));
        }
        
        return JsonResponse::create(new JsonContent('Вы успешно авторизованы.', true));
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function forgotPasswordAction(Request $request) : JsonResponse
    {
        $rawLogin = $request->request->get('login') ?? '';
        
        try {
            $this->_userService->restorePassword($rawLogin);
        } catch (TooManyUserFoundException $e) {
            return JsonResponse::create(new JsonContent('Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.',
                                                        false));
        } catch (\Exception $e) {
            return JsonResponse::create(new JsonContent('Неверный логин или пароль.', false));
        }
        
        return JsonResponse::create(new JsonContent('Инструкция по восстановлению пароля успешно отправлена.', true));
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerAction(Request $request) : JsonResponse
    {
        $data = [];
        
        try {
            $this->_userService->register($data);
        } catch (RegisterException $e) {
        
        } catch (\Exception $e) {
            return JsonResponse::create(new JsonContent('Системная ошибка при попытке регистрации. Пожалуйста, обратитесь к администратору сайта.',
                                                        false));
        }
        
        return JsonResponse::create(new JsonContent('Вы успешно Зарегистрированы.', true));
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changePasswordAction(Request $request) : JsonResponse
    {
        $password = $request->request->get('password') ?? '';
        $confirm  = $request->request->get('confirm') ?? '';
        
        $isAuthorized = $this->_userService->isAuthorized();
        
        try {
            if ($isAuthorized) {
                $user = $this->_userService->getCurrentUser();
                
                $login     = $user->getLogin();
                $checkword = $user->getCheckword();
            } else {
                $login     = $request->request->get('login') ?? '';
                $checkword = $request->request->get('checkword') ?? '';
            }
            
            $result = $this->_userService->changePassword($login, $checkword, $password, $confirm);
            
            if (!$result->isSuccess()) {
                throw new ChangePasswordException(implode($result->getErrorMessages()));
            }
        } catch (ChangePasswordException $e) {
            return JsonResponse::create(new JsonContent($e->getMessage(), false));
        } catch (\Exception $e) {
            return JsonResponse::create(new JsonContent('Системная ошибка при попытке изменении пароля. Пожалуйста, обратитесь к администратору сайта.',
                                                        false));
        }
        
        return JsonResponse::create(new JsonContent('Пароль успешно изменен', true));
    }
    
}
