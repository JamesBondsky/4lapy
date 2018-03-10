<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
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
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
        $this->ajaxMess = $container->get('ajax.mess');
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
            'PERSONAL_PHONE'  => PhoneHelper::formatPhone($curUser->getPersonalPhone(), '+7 (%s%s%s) %s%s%s-%s%s-%s%s'),
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
        ];

        $this->includeComponentTemplate();

        return true;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxConfirmPhone(Request $request): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }
        $phone = $request->get('phone');
        $oldPhone = $request->get('oldPhone', '');
        try {
            $phone = PhoneHelper::normalizePhone($phone);
            if(!empty($oldPhone)){
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
        ];
        try {
            if ($this->currentUserProvider->getUserRepository()->updateData((int)$request->get('ID', 0), $data)) {
                try {
                    /** @var ManzanaService $manzanaService */
                    $manzanaService = $container->get('manzana.service');
                    $client = null;
                    if (empty($oldPhone)) {
                        $client = new Client();
                        $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                    } else {
                        try {
                            $contactId = $manzanaService->getContactIdByPhone(PhoneHelper::getManzanaPhone($oldPhone));
                            $client = new Client();
                            $client->contactId = $contactId;
                            $client->phone = $phone;
                        } catch (ManzanaServiceException $e) {
                            $client = new Client();
                            $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                        } catch (WrongPhoneNumberException $e) {
                            return $this->ajaxMess->getWrongPhoneNumberException();
                        }
                    }

                    if ($client instanceof Client) {
                        $manzanaService->updateContactAsync($client);
                    }
                }
                catch(\Exception $e){
                    $logger = LoggerFactory::create('manzana');
                    $logger->error('manzana error - '.$e->getMessage());
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
        } catch (SmsSendErrorException $e) {
            return $this->ajaxMess->getSmsSendErrorException();
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
                $mess = $this->ajaxGetConfirm($phone, (int)$request->get('ID', 0));
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
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $curUser = $userRepository->find($id);
            $data = ['PERSONAL_PHONE' => $phone];
            $oldPhone = '';
            if ($curUser !== null) {
                $oldPhone = $curUser->getPersonalPhone();
            }
            if ($oldPhone !== $phone) {
                $data['UF_PHONE_CONFIRMED'] = false;
            }
            $res = $userRepository->updateData($id, $data);
            if (!$res) {
                return $this->ajaxMess->getUpdateError();
            }

            if (!empty($oldPhone)) {
                //Посылаем смс о смененном номере телефона
                $text = 'Номер телефона в Личном кабинете изменен на ' . $phone . '. Если это не вы, обратитесь по тел. 8(800)7700022';
                $smsService = $container->get('sms.service');
                $smsService->sendSms($text, $oldPhone);
            }

            $mess = 'Телефон обновлен';

            try {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = $container->get(ConfirmCodeInterface::class);
                $res = $confirmService::sendConfirmSms($phone);
                if (!$res) {
                    return $this->ajaxMess->getSmsSendErrorException();
                }
            } catch (SmsSendErrorException $e) {
                return $this->ajaxMess->getSmsSendErrorException();
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (\RuntimeException|\Exception $e) {
                return $this->ajaxMess->getSystemError();
            }
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }

        return $mess;
    }
}
