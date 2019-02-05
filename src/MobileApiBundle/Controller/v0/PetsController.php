<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\UserPetAddRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetPhotoAddRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetPhotoDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetUpdateRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\Api\PetService as ApiPetService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PetsController extends FOSRestController
{

    /**
     * @var ApiPetService
     */
    private $apiPetService;

    public function __construct(ApiPetService $apiPetsService)
    {
        $this->apiPetService = $apiPetsService;
    }

    /**
     * @Rest\Get("/pets_category/")
     * @Rest\View()
     *
     * @throws \Exception
     */
    public function getPetCategoryAction()
    {
        return (new Response())->setData($this->apiPetService->getPetCategories());
    }

    /**
     * @Rest\Get("/user_pets/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @return Response
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getUserPetAction()
    {
        return (new Response())->setData($this->apiPetService->getUserPetAll());
    }

    /**
     * @Rest\Post("/user_pets/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserPetAddRequest $userPetAddRequest
     * @return Response
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function addUserPetAction(UserPetAddRequest $userPetAddRequest)
    {
        return (new Response())->setData($this->apiPetService->addUserPet($userPetAddRequest));
    }

    /**
     * @Rest\Put("/user_pets/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserPetUpdateRequest $userPetUpdateRequest
     * @return Response
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function updateUserPetAction(UserPetUpdateRequest $userPetUpdateRequest)
    {
        return (new Response())->setData($this->apiPetService->updateUserPet($userPetUpdateRequest));
    }

    /**
     * @Rest\Delete("/user_pets/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserPetDeleteRequest $userPetDeleteRequest
     * @return Response
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function deleteUserPetAction(UserPetDeleteRequest $userPetDeleteRequest)
    {
        return (new Response())->setData($this->apiPetService->deleteUserPet($userPetDeleteRequest));
    }

    /**
     * @Rest\Post("/user_pets_photo/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserPetPhotoAddRequest $userPetPhotoAddRequest
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function addUserPetPhotoAction(UserPetPhotoAddRequest $userPetPhotoAddRequest)
    {
        return (new Response())->setData($this->apiPetService->addUserPetPhoto($userPetPhotoAddRequest));
    }

    /**
     * @Rest\Delete("/user_pets_photo/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserPetPhotoDeleteRequest $userPetPhotoDeleteRequest
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deleteUserPetPhotoAction(UserPetPhotoDeleteRequest $userPetPhotoDeleteRequest)
    {
        return (new Response())->setData($this->apiPetService->deleteUserPetPhoto($userPetPhotoDeleteRequest));
    }
}
