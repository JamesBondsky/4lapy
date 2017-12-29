<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyDateException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
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
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider
    )
    {
        $this->currentUserProvider = $currentUserProvider;
    }
    
    /**
     * @Route("/login/", methods={"POST"})
     * @param Request $request
     *
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws WrongPhoneNumberException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     * @return JsonResponse
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
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/register/", methods={"POST"})
     * @param Request $request
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\External\Manzana\Exception\ContactUpdateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Bitrix\Main\SystemException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     * @return null|JsonResponse
     */
    public function registerAction(Request $request)
    {
        $action = $request->get('action');
        
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
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/forgotPassword/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
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
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/changePhone/", methods={"POST"})
     * @param Request $request
     *
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
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
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/changePassword/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request) : JsonResponse
    {
        $old_password     = $request->get('old_password', '');
        $password         = $request->get('password', '');
        $confirm_password = $request->get('confirm_password', '');
        
        if (empty($old_password) || empty($password) || empty($confirm_password)) {
            return JsonErrorResponse::createWithData(
                'Должны быть заполнены все поля',
                ['errors' => ['emptyData' => 'Должны быть заполнены все поля']]
            );
        }
        
        if (\strlen($password) < 6) {
            return JsonErrorResponse::createWithData(
                'Пароль должен содержать минимум 6 символов',
                ['errors' => ['notValidPasswordLength' => 'Пароль должен содержать минимум 6 символов']]
            );
        }
        
        if (!$this->currentUserProvider->getCurrentUser()->equalPassword($old_password)) {
            return JsonErrorResponse::createWithData(
                'Текущий пароль не соответствует введенному',
                ['errors' => ['notEqualOldPassword' => 'Текущий пароль не соответствует введенному']]
            );
        }
        
        if ($password !== $confirm_password) {
            return JsonErrorResponse::createWithData(
                'Пароли не соответсвуют',
                ['errors' => ['notEqualPasswords' => 'Пароли не соответсвуют']]
            );
        }
        
        if ($old_password === $password) {
            return JsonErrorResponse::createWithData(
                'Пароль не может быть таким же, как и текущий',
                ['errors' => ['equalWithOldPassword' => 'Пароль не может быть таким же, как и текущий']]
            );
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->update(
                SerializerBuilder::create()->build()->fromArray(['PASSWORD' => $password], User::class)
            );
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Произошла ошибка при обновлении',
                    ['errors' => ['updateError' => 'Произошла ошибка при обновлении']]
                );
            }
            
            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при обновлении ' . $e->getMessage(),
                ['errors' => ['updateError' => 'Произошла ошибка при обновлении ' . $e->getMessage()]]
            );
        } catch (ConstraintDefinitionException $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/changeData/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @return JsonResponse
     */
    public function changeDataAction(Request $request) : JsonResponse
    {
        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $data           = $request->request->getIterator()->getArrayCopy();
        
        if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
            return JsonErrorResponse::createWithData(
                'Некорректный email',
                ['errors' => ['wrongEmail' => 'Некорректный email']]
            );
        }
        
        $curUser = $userRepository->findBy(['EMAIL' => $data['EMAIL']], [], 1);
        if ($curUser instanceof User || (\is_array($curUser) && !empty($curUser))) {
            return JsonErrorResponse::createWithData(
                'Такой email уже существует',
                ['errors' => ['haveEmail' => 'Такой email уже существует']]
            );
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
                return JsonErrorResponse::createWithData(
                    'Произошла ошибка при обновлении',
                    ['errors' => ['updateError' => 'Произошла ошибка при обновлении']]
                );
            }
            
            $manzanaService       = App::getInstance()->getContainer()->get('manzana.service');
            $manzanaClient        = new Client();
            $manzanaClient->phone = $data['PERSONAL_PHONE'];
            /** @todo В каком формате передавать пол */
            $manzanaClient->genderCode = $data['PERSONAL_GENDER'];
            /** @todo В каком формате передавать дату рождения */
            $manzanaClient->birthDate  = $data['PERSONAL_BIRTHDAY'];
            $manzanaClient->lastName   = $data['LAST_NAME'];
            $manzanaClient->secondName = $data['SECOND_NAME'];
            $manzanaClient->firstName  = $data['NAME'];
            $manzanaService->updateContact($manzanaClient);
            
            try {
                $birthday = $profileClass->replaceRuMonth($user->getBirthday()->format('d #n# Y'));
            } catch (EmptyDateException $e) {
                $birthday = '';
            }
            
            return JsonSuccessResponse::createWithData(
                'Данные обновлены',
                [
                    'email'    => $user->getEmail(),
                    'fio'      => $user->getFullName(),
                    'gender'   => $user->getGenderText(),
                    'birthday' => $birthday,
                ]
            );
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при обновлении ' . $e->getMessage(),
                ['errors' => ['updateError' => 'Произошла ошибка при обновлении ' . $e->getMessage()]]
            );
        } catch (ConstraintDefinitionException $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
}
