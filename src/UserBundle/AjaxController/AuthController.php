<?php

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
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
            return JsonErrorResponse::create('Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.');
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
    public function changePasswordAction(Request $request)
    {
        if ($this->userAuthorization->isAuthorized()) {
            $password = $request->get('password', '');
            $confirm = $request->get('confirm_password', '');
            $old = $request->get('old_password', '');
            
            if(empty($password) || empty($confirm) || empty($old)){
                return JsonErrorResponse::create('Необходимо заполнить все поля');
            }
            if($this->currentUserProvider->equalPassword($old)){
                return JsonErrorResponse::create('Введенный вами пароль не совпадает с текущим');
            }
            if($confirm !== $password){
                return JsonErrorResponse::create('Пароли не совпадают');
            }
        } else {
            return JsonErrorResponse::create('Вы не авторизованы');
        }
    }
    
    /**
     * @Route("/changePersonalData/", methods={"POST"})
     * @param Request $request
     */
    public function changePersonalDataAction(Request $request)
    {
    
    }
    
    /**
     * @Route("/changePhone/", methods={"POST"})
     * @param Request $request
     */
    public function changePhoneAction(Request $request)
    {
    
    }
}
