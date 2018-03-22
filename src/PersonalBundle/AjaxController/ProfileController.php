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
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\DateHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyDateException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\Serializer;
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

    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider,
        AjaxMess $ajaxMess
    ) {
        $this->userAuthorization = $userAuthorization;
        $this->currentUserProvider = $currentUserProvider;
        $this->ajaxMess = $ajaxMess;
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
        }
        catch (NotAuthorizedException $e){
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        if($id !== $curUser->getId()){
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
        }catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
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
                if ($user instanceof User && $user->allowedEASend()) {
                    if (!$expertSenderService->sendChangePasswordByProfile($user)) {
                        $logger = LoggerFactory::create('expertSender');
                        $logger->error('Произошла ошибка при отправке письма - смена пароля');
                    }
                } elseif (!$user->allowedEASend()) {
                    $logger = LoggerFactory::create('expertSender');
                    $logger->info('email ' . $user->getEmail() . ' не подтвержден');
                }
            }
            catch (ExpertsenderServiceException $e) {
                $logger = LoggerFactory::create('expertSender');
                $logger->error('ES don`t work - ' . $e->getMessage());
            }
            catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            }


            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
            /** скипаем для показа системной ошибки */
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/changeData/", methods={"POST"})
     * @param Request    $request
     *
     * @param Serializer $serializer
     *
     * @return JsonResponse

     */
    public function changeDataAction(Request $request, Serializer $serializer): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();

        try{
            $curUser = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e){
            return $this->ajaxMess->getNeedAuthError();
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        if (!empty($data['ID'])) {
            $data['ID'] = (int)$data['ID'];
        }
        /** @var User $user */
        $user = $serializer->fromArray($data, User::class);

        if($user->getId() !== $curUser->getId()){
            return $this->ajaxMess->getSecurityError();
        }

        $data = $request->request->all();
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
            try {
                $curUser = $userRepository->find($user->getId());
                if ($curUser !== null && $curUser->getEmail() !== $user->getEmail()) {
                    $data['UF_EMAIL_CONFIRMED'] = false;
                }
            }
            catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
                return $this->ajaxMess->getSystemError();
            }
            try {
                $res = $userRepository->updateData($user->getId(), $userRepository->prepareData($data));
                if (!$res) {
                    return $this->ajaxMess->getUpdateError();
                }
            } catch (BitrixRuntimeException $e) {
                return $this->ajaxMess->getUpdateError($e->getMessage());
            } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            }

            if ($user->allowedEASend()) {
                if ($user->getEmail() !== $curUser->getEmail()) {
                    try {
                        $expertSenderService = $container->get('expertsender.service');
                        $expertSenderService->sendChangeEmail($curUser, $user);
                    } catch (ExpertsenderServiceException $e) {
                        $logger = LoggerFactory::create('expertsender');
                        $logger->error('expertsender error:' . $e->getMessage());
                    }
                }
            } else {
                $logger = LoggerFactory::create('expertsender');
                $logger->info('email ' . $curUser->getEmail() . ' не подтвержден');
            }

            /** @var ManzanaService $manzanaService */
            try {
                $manzanaService = $container->get('manzana.service');
                $client = null;
                try {
                    $contactId = $manzanaService->getContactIdByUser();
                    $client = new Client();
                    $client->contactId = $contactId;
                } catch (ManzanaServiceException $e) {
                    $client = new Client();
                }

                if ($client instanceof Client) {
                    $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                    $manzanaService->updateContactAsync($client);
                }
            } catch (ApplicationCreateException $e) {
                return $this->ajaxMess->getSystemError();
            }

            try {
                $curBirthday = $user->getBirthday();
                if ($curBirthday instanceof Date) {
                    $birthday = DateHelper::replaceRuMonth($curBirthday->format('d #n# Y'), DateHelper::GENITIVE);
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
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }
    }
}
