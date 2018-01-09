<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyDateException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProfileController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        $this->currentUserProvider = $currentUserProvider;
    }
    
    /**
     * @Route("/changePhone/", methods={"POST"})
     * @param Request $request
     *
     * @throws ContactUpdateException
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
     * @throws NotAuthorizedException
     * @return JsonResponse
     */
    public function changeDataAction(Request $request) : JsonResponse
    {
        /** @var \FourPaws\UserBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $data           = $request->request->getIterator()->getArrayCopy();
        if (!empty($data[''])) {
            if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
                return JsonErrorResponse::createWithData(
                    'Некорректный email',
                    ['errors' => ['wrongEmail' => 'Некорректный email']]
                );
            }
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
            
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
            $contactId      = $manzanaService->getContactIdByCurUser();
            if ($contactId >= 0) {
                $client = new Client();
                if ($contactId > 0) {
                    $client->contactId = $contactId;
                }
                $this->currentUserProvider->setClientPersonalDataByCurUser($client, $user);
                $manzanaService->updateContact($client);
            }
            
            try {
                $curBirthday = $user->getBirthday();
                if ($curBirthday instanceof Date) {
                    $birthday = $profileClass->replaceRuMonth($curBirthday->format('d #n# Y'));
                } else {
                    $birthday = '';
                }
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
