<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
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

    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct(
        UserAuthorizationInterface $userAuthorization,
        CurrentUserProviderInterface $currentUserProvider,
        AjaxMess $ajaxMess
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/changePhone/", methods={"POST","GET"})
     * @param Request $request
     *
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function changePhoneAction(Request $request): JsonResponse
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
        return $this->ajaxMess->getSystemError();
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
    public function changePasswordAction(Request $request): JsonResponse
    {
        $id = (int)$request->get('ID', 0);
        $old_password = $request->get('old_password', '');
        $password = $request->get('password', '');
        $confirm_password = $request->get('confirm_password', '');

        if (empty($old_password) || empty($password) || empty($confirm_password)) {
            return $this->ajaxMess->getEmptyDataError();
        }

        if (\strlen($password) < 6) {
            return $this->ajaxMess->getPasswordLengthError(6);
        }

        if (!$this->currentUserProvider->getCurrentUser()->equalPassword($old_password)) {
            return $this->ajaxMess->getNotEqualOldPasswordError();
        }

        if ($password !== $confirm_password) {
            return $this->ajaxMess->getNotEqualPasswordError();
        }

        if ($old_password === $password) {
            return $this->ajaxMess->getNotEqualOldPasswordError();
        }

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->updatePassword($id, $password);
            if (!$res) {
                return $this->ajaxMess->getUpdateError();
            }

            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ConstraintDefinitionException $e) {
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
     * @throws ApplicationCreateException
     */
    public function changeDataAction(Request $request, Serializer $serializer): JsonResponse
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $data = $request->request->all();
        if (!empty($data['EMAIL']) && filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        if(!empty($data['ID'])){
            $data['ID'] = (int)$data['ID'];
        }
        /** @var User $user */
        $user = $serializer->fromArray($data, User::class);

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
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $curUser = $userRepository->find($user->getId());
            if ($curUser->getEmail() !== $user->getEmail()) {
                $data['UF_EMAIL_CONFIRMED'] = 'N';
            }
            $res = $userRepository->updateData($user->getId(), $userRepository->prepareData($data));
            if (!$res) {
                return $this->ajaxMess->getUpdateError();
            }

            /** @var ManzanaService $manzanaService */
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
            $client = null;
            try {
                $contactId = $manzanaService->getContactIdByCurUser();
                $client = new Client();
                $client->contactId = $contactId;
            } catch (ManzanaServiceException $e) {
                $client = new Client();
            }

            if ($client instanceof Client) {
                $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                $manzanaService->updateContactAsync($client);
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
        catch(\RuntimeException $e){}
        catch(\InvalidArgumentException $e){}

        return $this->ajaxMess->getSystemError();
    }
}
