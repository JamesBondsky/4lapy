<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Security\SecurityException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/address")
 */
class AddressController extends Controller
{
    /**
     * @var AddressService
     */
    private $addressService;

    /** @var AjaxMess */
    private $ajaxMess;
    /** @var UserAuthorizationInterface */
    private $userAuthorization;

    public function __construct(
        AddressService $addressService,
        UserAuthorizationInterface $userAuthorization,
        AjaxMess $ajaxMess
    ) {
        $this->addressService = $addressService;
        $this->userAuthorization = $userAuthorization;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function addAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        $data = $request->request->all();
        if (empty($data)) {
            return $this->ajaxMess->getEmptyDataError();
        }

        try {
            if ($this->addressService->addFromArray($data)) {
                return JsonSuccessResponse::create(
                    'Адрес доставки добавлен',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getAddError($e->getMessage());
        } catch (EmptyEntityClass $e) {
            return $this->ajaxMess->getAddError();
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }


        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/update/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        $data = $request->request->all();
        if (empty($data)) {
            return $this->ajaxMess->getEmptyDataError();
        }
        if ((int)$data['ID'] < 1) {
            return $this->ajaxMess->getNotIdError(' для обновления');
        }
        try {
            if ($this->addressService->update($data)) {
                return JsonSuccessResponse::create(
                    'Адрес доставки обновлен',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (SecurityException|NotFoundException $e) {
            return $this->ajaxMess->getSecurityError();
            /** показываем системную ошибку */
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (EmptyEntityClass $e) {
            return $this->ajaxMess->getUpdateError();
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException|ObjectPropertyException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/delete/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        $delId = (int)$request->get('id');
        if ($delId < 1) {
            return $this->ajaxMess->getNotIdError(' для удаления');
        }
        try {
            if ($this->addressService->delete($delId)) {
                return JsonSuccessResponse::create(
                    'Адрес доставки удален',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (SecurityException|NotFoundException $e) {
            return $this->ajaxMess->getSecurityError();
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getDeleteError($e->getMessage());
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException|ObjectPropertyException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
