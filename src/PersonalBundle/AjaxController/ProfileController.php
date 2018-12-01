<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\Helpers\DateHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\EventController\Event;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyDateException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\ArrayTransformerInterface;
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

    /** @var AjaxMess */
    private $ajaxMess;
    /** @var UserAuthorizationInterface */
    private $userAuthorization;
    /** @var ArrayTransformerInterface */
    private $arrayTransformer;

    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider,
        AjaxMess $ajaxMess,
        ArrayTransformerInterface $arrayTransformer
    ) {
        $this->userAuthorization = $userAuthorization;
        $this->currentUserProvider = $currentUserProvider;
        $this->ajaxMess = $ajaxMess;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @Route("/changePhone/", methods={"POST","GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePhoneAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }

        $action = $request->get('action', '');

        \CBitrixComponent::includeComponentClass('fourpaws:personal.profile');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $profileClass = new \FourPawsPersonalCabinetProfileComponent();
        } catch (SystemException|\RuntimeException|ServiceNotFoundException $e) {
            return $this->ajaxMess->getSystemError();
        }

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
        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/changePassword/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }

        $id = (int)$request->get('ID', 0);
        $old_password = $request->get('old_password', '');
        $password = $request->get('password', '');
        $confirm_password = $request->get('confirm_password', '');

        try {
            $curUser = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            return $this->ajaxMess->getSystemError();
        }

        if ($id !== $curUser->getId()) {
            return $this->ajaxMess->getSecurityError();
        }

        if (empty($old_password) || empty($password) || empty($confirm_password)) {
            return $this->ajaxMess->getEmptyDataError();
        }

        if (\strlen($password) < 6) {
            return $this->ajaxMess->getPasswordLengthError(6);
        }

        try {
            if (!$this->currentUserProvider->getCurrentUser()->equalPassword($old_password)) {
                return $this->ajaxMess->getNotEqualOldPasswordError();
            }
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
        }

        if ($password !== $confirm_password) {
            return $this->ajaxMess->getNotEqualPasswordError();
        }

        if ($old_password === $password) {
            return $this->ajaxMess->getEqualWithOldPasswordError();
        }

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->updatePassword($id, $password);
            if (!$res) {
                return $this->ajaxMess->getUpdateError();
            }

            try {
                $expertSenderService = App::getInstance()->getContainer()->get('expertsender.service');
                $user = $this->currentUserProvider->getUserRepository()->find($id);
                if ($user instanceof User && $user->hasEmail() && !$expertSenderService->sendChangePasswordByProfile($user)) {
                    $logger = LoggerFactory::create('expertSender');
                    $logger->error('Произошла ошибка при отправке письма - смена пароля');
                }
            } catch (ExpertsenderServiceException $e) {
                $logger = LoggerFactory::create('expertSender');
                $logger->error('ES don`t work - ' . $e->getMessage());
            } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            }


            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ConstraintDefinitionException|SystemException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            /** скипаем для показа системной ошибки */
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/disableModalPersist/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function disableModalPersist(Request $request): JsonResponse
    {
        global $USER;
        $USER->SetParam('data_collect', 'Y');

        $total_modals = $request->request->get('modals');

        $user_class = new \CUser;
        $user_id = (int) $GLOBALS['USER']->GetID();
        $user_class->Update($user_id, ['UF_MODALS_CNTS' => $total_modals]);

        return JsonSuccessResponse::createWithData('All fine!');
    }

    /**
     * @Route("/collectUserData/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function collectUserData(Request $request): JsonResponse
    {
        // Отправляем запрос на изменение данных, если пришел успешный ответ
        // Тогда отправим запрос на смену телефона и вывод модалки
        // шторм ругается на отсутствие JSON'a , возможно потребуется поставить.
        $update_response = $this->changeDataAction($request);
        $update_result = $update_response->getContent();
        $message = json_decode($update_result);

        if($message->message == 'Данные обновлены') // Прошли валидацию и записали данные пользователя
        {
            return $this->changePhoneAction($request);
        }
        return $update_response;
    }

    /**
     * @Route("/changeData/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changeDataAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();

        try {
            $curUser = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            return $this->ajaxMess->getSystemError();
        }

        $data = $request->request->all();

        if (!empty($data['ID'])) {
            $data['ID'] = (int)$data['ID'];
        }
        /** @var User $user */
        $user = $this->arrayTransformer->fromArray($data, User::class);

        if($user->getId() === 0){
            return $this->ajaxMess->getSecurityError();
        }

        if ($user->getId() !== $curUser->getId()) {
            return $this->ajaxMess->getSecurityError();
        }

        if (!empty($data['EMAIL']) && filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'EMAIL' => $user->getEmail(),
                'ID'    => $user->getId(),
            ]
        );
        if ($haveUsers['email']) {
            return $this->ajaxMess->getHaveEmailError();
        }

        try {
            try {
                $container = App::getInstance()->getContainer();
            } catch (ApplicationCreateException $e) {
                return $this->ajaxMess->getSystemError();
            }
            /** отключаем снятие чека при смене email
             * @todo сделать его после успешной отправки через сендер, а тут снимать
             */
//            if ($curUser !== null && $curUser->getEmail() !== $user->getEmail()) {
//                $data['UF_EMAIL_CONFIRMED'] = false;
//            }
            try {
                /** обновление данных манзаны сработает на событии @see Event::updateManzana() */
                $updateData = $userRepository->prepareData($data);
                if(empty($data['PERSONAL_BIRTHDAY'])){
                    $updateData['PERSONAL_BIRTHDAY'] = '';
                }
                $res = $userRepository->updateData($user->getId(), $updateData);
                if (!$res) {
                    return $this->ajaxMess->getUpdateError();
                }
            } catch (BitrixRuntimeException $e) {
                return $this->ajaxMess->getUpdateError($e->getMessage());
            } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
                return $this->ajaxMess->getUpdateError();
            } catch (SystemException $e) {
                $logger = LoggerFactory::create('system');
                $logger->error('Системная ошибка - ' . $e->getMessage());
                return $this->ajaxMess->getUpdateError();
            }

            $isSend = false;
            if ($user->hasEmail() && $user->getEmail() !== $curUser->getEmail()) {
                try {
                    $expertSenderService = $container->get('expertsender.service');
                    $expertSenderService->sendChangeEmail($curUser, $user);
                    $isSend = true;
                } catch (ExpertsenderServiceException $e) {
                    $logger = LoggerFactory::create('expertsender');
                    $logger->error('expertsender error:' . $e->getMessage());
                }
            }

            if(!$isSend && ($curUser->getLastName() !== $user->getLastName() || $curUser->getName() !== $user->getName())){
                try {
                    $expertSenderService = $container->get('expertsender.service');
                    $expertSenderService->changeUserData($user);
                } catch (ExpertsenderServiceException $e) {
                    $logger = LoggerFactory::create('expertsender');
                    $logger->error('expertsender error:' . $e->getMessage());
                }
            }

            try {
                $curBirthday = $user->getBirthday();
                if ($curBirthday instanceof Date) {
                    $birthday = DateHelper::replaceRuMonth($curBirthday->format('d #n# Y'), DateHelper::GENITIVE);
                    $birthdayFormatted = DateHelper::replaceRuMonth($curBirthday->format('d.m.Y'), DateHelper::GENITIVE);
                } else {
                    $birthday = '';
                    $birthdayFormatted = '';
                }
            } catch (EmptyDateException $e) {
                $birthday = '';
                $birthdayFormatted = '';
            }

            return JsonSuccessResponse::createWithData(
                'Данные обновлены',
                [
                    'email'    => $user->getEmail(),
                    'fio'      => $user->getFullName(),
                    'gender'   => $user->getGenderText(),
                    'birthday' => $birthday,
                    'birthdayFormatted' => $birthdayFormatted,
                    'firstName' => $user->getName(),
                    'lastName' => $user->getLastName(),
                    'patronymic' => $user->getSecondName(),
                ]
            );
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }
    }
}
