<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Model\ResponseContent\JsonContent;
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
        $rawLogin = $request->query->get('login') ?? '';
        $password = $request->query->get('password') ?? '';
        
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
    
}
