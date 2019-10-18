<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Security\SecurityException;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
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

    /**
     * @var AjaxMess
     */
    private $ajaxMess;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorization;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(
        AddressService $addressService,
        LocationService $locationService,
        UserAuthorizationInterface $userAuthorization,
        AjaxMess $ajaxMess
    ) {
        $this->addressService = $addressService;
        $this->locationService = $locationService;
        $this->userAuthorization = $userAuthorization;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function addAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }

        $fields = $request->request->all();
        if ($location = $this->getDadataLocation($request, $fields)['result']) {
            $fields['UF_CITY'] = $location->getName();
            $fields['UF_CITY_LOCATION'] = $location->getCode();
        }

        try {
            if ($this->addressService->addFromArray($fields)) {
                return JsonSuccessResponse::create(
                    'Адрес доставки добавлен',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (CityNotFoundException $e) {
            return $this->ajaxMess->getAddError(' населенный пункт не найден');
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
     * @throws ApplicationCreateException
     */
    public function updateAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }

        $fields = $request->request->all();
        $locationResult = $this->getDadataLocation($request, $fields);
        if ($location =$locationResult['result']) {
            $fields['UF_CITY'] = $location->getName();
            $fields['UF_CITY_LOCATION'] = $location->getCode();
        }

        try {
            if ($this->addressService->update($fields, !$locationResult['is_Moscow_district'])) {
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

    /**
     * @param Request $request
     *
     * @param array $fields
     * @return BitrixLocation
     * @throws ApplicationCreateException
     */
    protected function getDadataLocation(Request $request, array $fields): array
    {
        $result = null;
        $isMoscowDistrict = false;

        try {
            $dadata = json_decode($request->get('dadata', ''), true);
            if ($dadata && \is_array($dadata)) {
                $result = $this->locationService->getCityFromDadata($dadata);
            }
        } catch (CityNotFoundException $e) {
        }

        /* Если Москва, то пробуем найти район в дадате */
        if ($this->isMoscowLocation($result, $fields) && $this->isValidAddressFields($fields)) {
            $strAddress = sprintf('%s, %s, %s', $fields['UF_CITY'], $fields['UF_STREET'], $fields['UF_HOUSE']);
            try {
                $okato = $this->locationService->getDadataLocationOkato($strAddress);
                $locations = $this->locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);

                if (count($locations)) {
                    $location = current($locations);

                    if (($locationCode = $location['CODE']) && (!empty($locationCode))) {
                        $isMoscowDistrict = true;
                        if ($result === null) {
                            $result = new BitrixLocation();
                            $result->setName($fields['UF_CITY']);
                        }
                        $result->setCode($locationCode);
                    }
                }

            } catch (\Exception $e) {
            }
        }

        return [
            'result' => $result,
            'is_Moscow_district' => $isMoscowDistrict,
        ];
    }

    /**
     * @param BitrixLocation $bitrixLocation
     * @param array $fields
     * @return bool
     */
    protected function isMoscowLocation(?BitrixLocation $bitrixLocation, array $fields): bool
    {
        if ($bitrixLocation && $bitrixLocation->getCode() === LocationService::LOCATION_CODE_MOSCOW) {
            return true;
        }

        if (ToUpper($fields['UF_CITY']) === 'МОСКВА') {
            return true;
        }

        return false;
    }

    protected function isValidAddressFields(array $fields): bool
    {
        return (isset($fields['UF_CITY'], $fields['UF_STREET'], $fields['UF_HOUSE']) && !empty($fields['UF_CITY']) && !empty($fields['UF_STREET']) && !empty($fields['UF_HOUSE']));
    }
}
