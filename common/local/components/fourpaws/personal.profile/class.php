<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */

class FourPawsPersonalCabinetProfileComponent extends CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /** @var AjaxMess */
    private $ajaxMess;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
            $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
            $this->ajaxMess = $container->get('ajax.mess');
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws BitrixRuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);

        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return null;
        }

        try {
            $instance = Application::getInstance();
        } catch (SystemException $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            return null;
        }

        if ($this->startResultCache($this->arParams['CACHE_TIME'],
            ['USER_ID' => $this->currentUserProvider->getCurrentUserId()])) {
            $curUser = $this->currentUserProvider->getCurrentUser();

            $curBirthday = $curUser->getBirthday();
            if ($curBirthday instanceof Date) {
                try {
                    $birthday = DateHelper::replaceRuMonth($curBirthday->format('j #n# Y'), DateHelper::GENITIVE);
                } catch (\Exception $e) {
                    $birthday = '';
                }
            } else {
                $birthday = '';
            }

            $this->arResult['CUR_USER'] = [
                'ID'              => $curUser->getId(),
                'PERSONAL_PHONE'  => PhoneHelper::formatPhone($curUser->getPersonalPhone(),
                    '+7 (%s%s%s) %s%s%s-%s%s-%s%s'),
                'EMAIL'           => $curUser->getEmail(),
                'FULL_NAME'       => $curUser->getFullName(),
                'LAST_NAME'       => $curUser->getLastName(),
                'NAME'            => $curUser->getName(),
                'SECOND_NAME'     => $curUser->getSecondName(),
                'GENDER'          => $curUser->getGender(),
                'GENDER_TEXT'     => $curUser->getGenderText(),
                'BIRTHDAY'        => $birthday,
                'BIRTHDAY_POPUP'  => ($curBirthday instanceof Date) ? $curBirthday->format(
                    'd.m.Y'
                ) : '',
                'EMAIL_CONFIRMED' => $curUser->isEmailConfirmed(),
                'PHONE_CONFIRMED' => $curUser->isPhoneConfirmed(),
                'ADDRESS'         => $curUser->getAddress(),
            ];

            TaggedCacheHelper::addManagedCacheTags([
                'personal:profile:' . $curUser->getId(),
                'user:' . $curUser->getId(),
            ]);

            $this->includeComponentTemplate();
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxConfirmPhone(Request $request): JsonResponse
    {
        $userId = (int)$request->get('ID', 0);

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

        if ($userId !== $curUser->getId()) {
            return $this->ajaxMess->getSecurityError();
        }

        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }
        $phone = $request->get('phone');
        $oldPhone = $request->get('oldPhone', '');
        try {
            $phone = PhoneHelper::normalizePhone($phone);
            if (!empty($oldPhone)) {
                $oldPhone = PhoneHelper::normalizePhone($oldPhone);
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = $container->get(ConfirmCodeInterface::class);
            $res = $confirmService::checkConfirmSms(
                $phone,
                $request->get('confirmCode')
            );
            if (!$res) {
                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (NotFoundConfirmedCodeException $e) {
            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        } catch (Exception $e) {
            return $this->ajaxMess->getSystemError();
        }
        $data = [
            'UF_PHONE_CONFIRMED' => true,
            'PERSONAL_PHONE'     => $phone,
        ];

        try {
            /** обновление данных манзаны сработает на событии @see Event::updateManzana() */
            if ($this->currentUserProvider->getUserRepository()->updateData($userId, $data)) {
                TaggedCacheHelper::clearManagedCache(['personal:profile:' . $userId]);

                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */

                if (!empty($oldPhone)) {
                    //Посылаем смс о смененном номере телефона
                    $text = 'Номер телефона в Личном кабинете изменен на ' . $phone . '. Если это не вы, обратитесь по тел. 8(800)7700022';
                    $smsService = $container->get('sms.service');
                    $smsService->sendSms($text, $oldPhone);
                }

                return JsonSuccessResponse::create('Телефон верифицирован');
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (\Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        return $this->ajaxMess->getVerificationError();
    }

    /**
     * @param string $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms(string $phone): JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res = $confirmService::sendConfirmSms($phone);
            if (!$res) {
                return $this->ajaxMess->getSmsSendErrorException();
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (\RuntimeException|\Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        return JsonSuccessResponse::create('Смс успешно отправлено');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxGet(Request $request): JsonResponse
    {
        $userId = (int)$request->get('ID', 0);
        $phone = $request->get('phone', '');
        $step = $request->get('step', '');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $oldPhone = $request->get('oldPhone', '');
        $mess = '';
        try {
            $phone = PhoneHelper::normalizePhone($phone);
            if (!empty($oldPhone)) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $oldPhone = PhoneHelper::normalizePhone($oldPhone);
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }
        switch ($step) {
            case 'confirm':
                $mess = $this->ajaxGetConfirm($phone, $userId);
                if ($mess instanceof JsonResponse) {
                    return $mess;
                }
                break;
        }

        $phone = PhoneHelper::formatPhone($phone, '+7 (%s%s%s) %s%s%s-%s%s-%s%s');
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
            . '/local/components/fourpaws/personal.profile/templates/popupChangePhone/include/' . $step
            . '.php';
        $html = ob_get_clean();

        return JsonSuccessResponse::createWithData(
            $mess,
            [
                'html'  => $html,
                'step'  => $step,
                'phone' => $phone ?? '',
            ]
        );
    }

    /**
     * @param string $phone
     * @param int    $id
     *
     * @return JsonResponse|string
     */
    private function ajaxGetConfirm(string $phone, int $id)
    {
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

        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $phone,
                'ID'             => $id,
            ]
        );
        if ($haveUsers['phone']) {
            return $this->ajaxMess->getHavePhoneError();
        }

        try {
            $container = App::getInstance()->getContainer();

            $mess = 'Начато обновление телефона';

            try {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = $container->get(ConfirmCodeInterface::class);
                $res = $confirmService::sendConfirmSms($phone);
                if (!$res) {
                    return $this->ajaxMess->getSmsSendErrorException();
                }
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (\RuntimeException|\Exception $e) {
                return $this->ajaxMess->getSystemError();
            }
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            return $this->ajaxMess->getSystemError();
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }

        return $mess;
    }
}
