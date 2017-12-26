<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetProfileComponent extends CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
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
    }
    
    /**
     * {@inheritdoc}
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        /** @var UserService $userService */
        $userService =
            App::getInstance()->getContainer()->get(\FourPaws\UserBundle\Service\CurrentUserProviderInterface::class);
        if (!$userService->isAuthorized()) {
            return null;
        }
        
        $curUser                    = $userService->getCurrentUser();
        $this->arResult['CUR_USER'] = [
            'PERSONAL_PHONE'  => $curUser->getPersonalPhone(),
            'EMAIL'           => $curUser->getEmail(),
            'FULL_NAME'       => $curUser->getFullName(),
            'LAST_NAME'       => $curUser->getLastName(),
            'NAME'            => $curUser->getName(),
            'SECOND_NAME'     => $curUser->getSecondName(),
            'GENDER'          => $curUser->getGender(),
            'GENDER_TEXT'     => $curUser->getGenderText(),
            'BIRTHDAY'        => $this->replaceRuMonth($curUser->getBirthday()->format('j #n# Y')),
            'EMAIL_CONFIRMED' => $curUser->isEmailConfirmed(),
            'PHONE_CONFIRMED' => $curUser->isPhoneConfirmed(),
        ];
        
        $this->includeComponentTemplate();
        
        return true;
    }
    
    /**
     * @param string $date
     *
     * @return string
     */
    public function replaceRuMonth(string $date) : string
    {
        /** @todo Русская локаль не помогла - может можно по другому? */
        $months = [
            '#1#'  => 'Января',
            '#2#'  => 'Февраля',
            '#3#'  => 'Марта',
            '#4#'  => 'Апреля',
            '#5#'  => 'Мая',
            '#6#'  => 'Июня',
            '#7#'  => 'Июля',
            '#8#'  => 'Августа',
            '#9#'  => 'Сентября',
            '#10#' => 'Октября',
            '#11#' => 'Ноября',
            '#12#' => 'Декабря',
        ];
        preg_match('|#[0-9]{1,2}#|', $date, $matches);
        if (!empty($matches[0])) {
            return str_replace($matches[0], $months[$matches[0]], $date);
        }
        
        return $date;
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function ajaxConfirmPhone(Request $request) : JsonResponse
    {
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::checkConfirmSms(
                $request->get('phone'),
                $request->get('confirmCode')
            );
            if (!$res) {
                return JsonErrorResponse::create(
                    'Код подтверждения не соответствует'
                );
            }
        } catch (ExpiredConfirmCodeException $e) {
            return JsonErrorResponse::create(
                $e->getMessage()
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create(
                $e->getMessage()
            );
        }
        $data = ['UF_PHONE_CONFIRMED' => 'Y'];
        try {
            if ($this->currentUserProvider->getUserRepository()->update(
                SerializerBuilder::create()->build()->fromArray($data, User::class)
            )) {
                /** todo отправить данные в манзану о пользователе */
                /** @var ManzanaService $manzanaService */
                $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
                $manzanaService->updateContact([]);
                
                return JsonSuccessResponse::create('Телефон верифицирован');
            }
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        } catch (ApplicationCreateException $e) {
        } catch (ServiceCircularReferenceException $e) {
        }
        
        return JsonErrorResponse::create('Ошибка верификации');
    }
    
    /**
     * @param string $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms(string $phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::create($e->getMessage());
            }
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::create(
                    'Ошибка отправки смс, попробуйте позднее'
                );
            }
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        }
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws InvalidIdentifierException
     */
    public function ajaxGet(Request $request) : JsonResponse
    {
        $phone = $request->get('phone', '');
        $step  = $request->get('step', '');
        $mess  = '';
        switch ($step) {
            case 'confirm':
                $mess = $this->ajaxGetConfirm($phone);
                if ($mess instanceof JsonResponse) {
                    return $mess;
                }
                break;
        }
        
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/personal.profile/templates/.default/include/'
                     . $step . '.php';
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
     *
     * @return string|JsonResponse
     */
    private function ajaxGetConfirm(string $phone)
    {
        $mess = '';
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonSuccessResponse::create('Неверный формат номера телефона');
        }
    
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $curUser        = $userRepository->findBy(['PERSONAL_PHONE' => $phone], [], 1);
        if ($curUser instanceof User || (\is_array($curUser) && !empty($curUser))) {
            return JsonErrorResponse::create('Такой телефон уже существует');
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $userRepository->update(
                SerializerBuilder::create()->build()->fromArray(['PERSONAL_PHONE' => $phone], User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
            
            $mess = 'Телефон обновлен';
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        }
        
        return $mess;
    }
}
