<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Type\Date;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\MobileApiBundle\Dto\Object\Pet;
use FourPaws\MobileApiBundle\Dto\Object\PetGender;
use FourPaws\MobileApiBundle\Dto\Object\PetPhoto;
use FourPaws\MobileApiBundle\Dto\Request\UserPetAddRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetPhotoAddRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetPhotoDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetUpdateRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\PetService as AppPetService;
use FourPaws\PersonalBundle\Repository\PetRepository;
use FourPaws\UserBundle\Service\UserService as UserBundleService;

class PetService
{
    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @var AppPetService
     */
    private $appPetService;

    /**
     * @var PetRepository
     */
    private $petRepository;

    /**
     * @var UserBundleService
     */
    private $userBundleService;

    public function __construct(
        CategoriesService $categoriesService,
        FilterService $filterService,
        AppPetService $appPetService,
        PetRepository $petRepository,
        UserBundleService $userBundleService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->filterService = $filterService;
        $this->appPetService = $appPetService;
        $this->petRepository = $petRepository;
        $this->userBundleService = $userBundleService;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
     */
    public function getPetCategories()
    {
        $result = [];

        $types = $this->appPetService->getPetTypes();
        $genders = [];
        foreach ($this->appPetService->getGenders() as $gender) {
            /** @var UserFieldEnumValue $gender */
            $genders[] = [
                'id' => $gender->getXmlId(),
                'title' => $gender->getValue()
            ];
        }
        $breeds = $this->appPetService->getPetBreedAll();
        foreach ($types as $type) {
            $result[$type['ID']] = [
                'id' => $type['ID'],
                'title' => $type['UF_NAME'],
                'gender' => $genders,
                'breeds' => []
            ];
            foreach ($breeds as $breed) {
                if ($breed['UF_PET_TYPE'] === $type['ID']) {
                    $result[$type['ID']]['breeds'][] = [
                        'id' => $breed['ID'],
                        'title' => $breed['UF_NAME']
                    ];
                }
            }
        }
        return array_values($result);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getUserPetAll()
    {
        return $this->appPetService->getCurUserPets()->map(\Closure::fromCallable([$this, 'map']));
    }

    /**
     * @param int $id
     * @return Pet
     * @throws NotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getUserPetById(int $id)
    {
        $pet = $this->getUserPetAll()->filter(function($pet) use($id) {
            /** @var Pet $pet */
            return $pet->getId() === $id;
        })->current();
        if (!$pet) {
            throw new NotFoundException("Питомец с ID $id не найден у текущего пользователя");
        }
        return $pet;
    }

    /**
     * @param UserPetAddRequest $addUserPetRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    public function addUserPet(UserPetAddRequest $addUserPetRequest)
    {
        $currentUser = $this->userBundleService->getCurrentUser();

        $petEntity = (new \FourPaws\PersonalBundle\Entity\Pet())
            ->setName($addUserPetRequest->getName())
            ->setType($addUserPetRequest->getCategoryId())
            ->setUserId($currentUser->getId())
            ->setBreed($addUserPetRequest->getBreedOther())
            ->setBreedId($addUserPetRequest->getBreedId())
           ;

        if ($birthday = $addUserPetRequest->getBirthday()) {
            $petEntity->setBirthday((new Date($birthday->format('d.m.Y'))));
        }

        if ($genderCode = $addUserPetRequest->getGender()) {
            if ($gender = $this->appPetService->getGenderByCode($genderCode)) {
                $petEntity->setGender($gender->getId());
            } else {
                throw new RuntimeException("Код пола питомца $genderCode является не корректным.");
            }
        }

        $this->petRepository->setEntity($petEntity);
        $this->petRepository->create();
        return $this->getUserPetAll();
    }

    /**
     * @param UserPetUpdateRequest $userPetUpdateRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    public function updateUserPet(UserPetUpdateRequest $userPetUpdateRequest)
    {
        $pet = $this->getUserPetById($userPetUpdateRequest->getId());

        $petEntity = (new \FourPaws\PersonalBundle\Entity\Pet());
        $petEntity->setId($pet->getId());
        $petEntity
            ->setName($userPetUpdateRequest->getName())
            ->setType($userPetUpdateRequest->getCategoryId())
            ->setBreed($userPetUpdateRequest->getBreedOther())
            ->setBreedId($userPetUpdateRequest->getBreedId())
        ;

        if ($birthday = $userPetUpdateRequest->getBirthday()) {
            $petEntity->setBirthday((new Date($birthday->format('d.m.Y'))));
        }

        if ($genderCode = $userPetUpdateRequest->getGender()) {
            if ($gender = $this->appPetService->getGenderByCode($genderCode)) {
                $petEntity->setGender($gender->getId());
            } else {
                throw new RuntimeException("Код пола питомца $genderCode является не корректным.");
            }
        }

        $this->petRepository->setEntity($petEntity);
        $this->petRepository->update();
        return $this->getUserPetAll();
    }

    /**
     * @param UserPetDeleteRequest $userPetDeleteRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    public function deleteUserPet(UserPetDeleteRequest $userPetDeleteRequest)
    {
        $pet = $this->getUserPetById($userPetDeleteRequest->getId());
        $this->petRepository->delete($pet->getId());
        return $this->getUserPetAll();
    }

    /**
     * @param UserPetPhotoAddRequest $userPetPhotoAddRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function addUserPetPhoto(UserPetPhotoAddRequest $userPetPhotoAddRequest)
    {
        $id = $userPetPhotoAddRequest->getPetId();
        $photo = $userPetPhotoAddRequest->getPhoto();
        $pet = $this->appPetService->getCurUserPetById($id);
        if (!$pet) {
            throw new NotFoundException("Питомец с ID $id не найден у текущего пользователя");
        }

        // toDo доделать загрузку фото
        // $fileData = \CFile::MakeFileArray($photo);
        // $photoId = \CFile::SaveFile($fileData);
        // $pet->setPhoto($photoId);

        $this->petRepository->setEntity($pet)->update();
        return $this->getUserPetAll();
    }

    /**
     * @param UserPetPhotoDeleteRequest $userPetPhotoDeleteRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function deleteUserPetPhoto(UserPetPhotoDeleteRequest $userPetPhotoDeleteRequest)
    {
        $id = $userPetPhotoDeleteRequest->getPetId();
        $pet = $this->appPetService->getCurUserPetById($id);
        if (!$pet) {
            throw new NotFoundException("Питомец с ID $id не найден у текущего пользователя");
        }
        if ($pet->getPhoto()) {
            $this->appPetService->deletePetPhoto($pet->getId());
        }
        return $this->getUserPetAll();
    }

    /**
     * @param \FourPaws\PersonalBundle\Entity\Pet $pet
     * @return Pet
     * @throws \Bitrix\Main\SystemException
     */
    public function map($pet)
    {
        $birthdayStmp = $pet->getBirthday()->getTimestamp();
        $birthday = (new \DateTime())->setTimestamp($birthdayStmp);
        $result = (new Pet())
            ->setId($pet->getId())
            ->setName($pet->getName())
            ->setCategoryId($pet->getType())
            ->setBreedId(intval($pet->getBreedId()))
            ->setBreedOther($pet->getBreed())
            ->setBirthday($birthday)
            ->setBirthdayString($pet->getAgeString())
            ->setPhoto(
                (new PetPhoto())
                    ->setId($pet->getPhoto())
                    ->setPreview($pet->getImgPath())
                    ->setSrc($pet->getResizePopupImgPath())
            )
        ;
        if ($genderCode = $pet->getGender()) {
            $result->setGender(
                (new PetGender())
                    ->setId($pet->getCodeGender())
                    ->setTitle($pet->getStringGender())
            );
        }
        return $result;
    }

}
