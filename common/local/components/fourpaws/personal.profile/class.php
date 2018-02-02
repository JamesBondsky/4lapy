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
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Exception\ManzanaException;
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
        $this->authUserProvider    = $container->get(UserAuthorizationInterface::class);
    }
    
    /**
     * {@inheritdoc}
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
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function ajaxConfirmPhone(Request $request) : JsonResponse
    {
        $phone    = $request->get('phone');
        $oldPhone = $request->get('oldPhone', '');
        try {
            $phone    = PhoneHelper::normalizePhone($phone);
            $oldPhone = PhoneHelper::normalizePhone($oldPhone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res            = $confirmService::checkConfirmSms(
                $phone,
                $request->get('confirmCode')
            );
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Код подтверждения не соответствует',
                    ['errors' => ['wrongConfirmCode' => 'Код подтверждения не соответствует']]
                );
            }
        } catch (ExpiredConfirmCodeException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['expiredConfirmCode' => $e->getMessage()]]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        } catch (NotFoundConfirmedCodeException $e) {
            return JsonErrorResponse::createWithData(
                'Не найден код подтверждения',
                ['errors' => ['notFoundConfirmedCode' => 'Не найден код подтверждения']]
            );
        }
        $data = [
            'UF_PHONE_CONFIRMED' => 'Y',
        ];
        try {
            if ($this->currentUserProvider->getUserRepository()->updateData((int)$request->get('ID', 0), $data)) {
                /** @var ManzanaService $manzanaService */
                $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
                $client         = null;
                if (empty($oldPhone)) {
                    return JsonErrorResponse::createWithData(
                        'Текущий телефон не задан',
                        ['errors' => ['notOldPhone' => 'Текущий телефон не задан']]
                    );
                }
                try {
                    $contactId         = $manzanaService->getContactIdByPhone($oldPhone);
                    $client            = new Client();
                    $client->contactId = $contactId;
                    $client->phone     = $phone;
                } catch (ManzanaServiceContactSearchMoreOneException $e) {
                } catch (ManzanaServiceContactSearchNullException $e) {
                    $client = new Client();
                    $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                } catch (ManzanaServiceException $e) {
                }
                if ($client instanceof Client) {
                    try {
                        /**
                         * @todo refactor it
                         */
                        $manzanaService->updateContact($client);
                    } catch (ManzanaServiceException $e) {
                    } catch (ManzanaException $e) {
                    }
                }
                
                return JsonSuccessResponse::create('Телефон верифицирован');
            }
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при обновлении ' . $e->getMessage(),
                [
                    'errors' => [
                        'updateError' => 'Произошла ошибка при обновлении ' . $e->getMessage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Ошибка верификации',
            ['errors' => ['verificationError' => 'Ошибка верификации']]
        );
    }
    
    /**
     * @param string $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms(string $phone) : JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res            = $confirmService::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Ошибка отправки смс, попробуйте позднее',
                    ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
                );
            }
        } catch (SmsSendErrorException $e) {
            return JsonErrorResponse::createWithData(
                'Ошибка отправки смс, попробуйте позднее',
                ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
        }
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param Request $request
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @return JsonResponse
     * @throws ConstraintDefinitionException
     */
    public function ajaxGet(Request $request) : JsonResponse
    {
        $phone = $request->get('phone', '');
        $step  = $request->get('step', '');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $oldPhone = $request->get('oldPhone', '');
        $mess     = '';
        try {
            $phone = PhoneHelper::normalizePhone($phone);
            /** @noinspection PhpUnusedLocalVariableInspection */
            $oldPhone = PhoneHelper::normalizePhone($oldPhone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
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
     * @throws ConstraintDefinitionException
     * @return JsonResponse|string
     */
    private function ajaxGetConfirm(string $phone, int $id)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $phone,
            ]
        );
        if($haveUsers['phone']){
            return JsonErrorResponse::createWithData(
                'Такой телефон уже существует',
                ['errors' => ['havePhone' => 'Такой телефон уже существует']]
            );
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $userRepository->updatePhone($id, $phone);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Произошла ошибка при обновлении',
                    ['errors' => ['updateError' => 'Произошла ошибка при обновлении']]
                );
            }
            
            $mess = 'Телефон обновлен';
            
            try {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $res            = $confirmService::sendConfirmSms($phone);
                if (!$res) {
                    return JsonErrorResponse::createWithData(
                        'Ошибка отправки смс, попробуйте позднее',
                        ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
                    );
                }
            } catch (SmsSendErrorException $e) {
                return JsonErrorResponse::createWithData(
                    'Ошибка отправки смс, попробуйте позднее',
                    ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
                );
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::createWithData(
                    'Некорректный номер телефона',
                    ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
                );
            } catch (\RuntimeException $e) {
                return JsonErrorResponse::createWithData(
                    'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                    ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
                );
            } catch (\Exception $e) {
                return JsonErrorResponse::createWithData(
                    'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                    ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
                );
            }
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при обновлении ' . $e->getMessage(),
                [
                    'errors' => [
                        'updateError' => 'Произошла ошибка при обновлении ' . $e->getMessage(),
                    ],
                ]
            );
        }
        
        return $mess;
    }
}
