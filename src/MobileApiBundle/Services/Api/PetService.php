<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Type\Date;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Dto\Object\Pet;
use FourPaws\MobileApiBundle\Dto\Object\PetGender;
use FourPaws\MobileApiBundle\Dto\Object\PetPhoto;
use FourPaws\MobileApiBundle\Dto\Request\UserPetAddRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserPetDeleteRequest;
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

    public function getPetCategories()
    {
        $arResult = array();

        $breeds = $this->getPetBreeds();
        $genders = $this->appPetService->getGenders();

        // toDo доделать когда в базе данных будет связь между категорией животного и его породой / полом
        var_dump($breeds);
        var_dump($genders);
        die();

        $oCategoryes = CIBlockSection::GetList(
            array(
                'LEFT_MARGIN' => 'ASC'
            ),
            array(
                'IBLOCK_ID' => CIBlockTools::GetIBlockId('kinds'),
                'ACTIVE' => 'Y'
            ),
            false,
            array(
                'ID',
                'NAME',
                'IBLOCK_SECTION_ID',
                'SORT',
                'UF_GENDER'
            )
        );

        while ($arCategory = $oCategoryes->Fetch())
        {
            $sid = $arCategory['ID'];
            $psid = (int)$arCategory['IBLOCK_SECTION_ID'];

            $arResult[$psid]['subcategories'][$sid] = array(
                'id' => $arCategory['ID'],
                'title' => $arCategory['NAME'],
                'gender' => array()
            );


            if (is_array($arCategory['UF_GENDER'])) {
                foreach ($arCategory['UF_GENDER'] as $sexId)
                {
                    $arResult[$psid]['subcategories'][$sid]['gender'][] = array(
                        'id' => (string)$sexId,
                        'title' => $arGenders[$sexId]
                    );
                }
            }

            if ($psid) {
                $arResult[$psid]['subcategories'][$sid]['gender'] = $arResult[$psid]['gender'];
                $arResult[$psid]['subcategories'][$sid]['breeds'] = (array)$arBreeds[$sid];
            } else {
                $arResult[$psid]['subcategories'][$sid]['sort'] = $arCategory['SORT'];
                $arResult[$psid]['gender'] = $arCategory['UF_GENDER'];
            }

            $arResult[$sid] = &$arResult[$psid]['subcategories'][$sid];
        }

        $arResult = array_shift($arResult);
        $arResult = array_shift($arResult);

        usort($arResult, $this->customSort);

        foreach ($arResult as $categoryId => $arCategory)
        {
            unset($arResult[$categoryId]['sort']);
            usort($arResult[$categoryId]['subcategories'], $this->customSort);
        }
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
            ->setBreed('Whatever')
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
            ->setBreed('Whatever')
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
     * @param \FourPaws\PersonalBundle\Entity\Pet $pet
     * @return Pet
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function map($pet)
    {
        $birthdayStmp = $pet->getBirthday()->getTimestamp();
        $birthday = (new \DateTime())->setTimestamp($birthdayStmp);
        $result = (new Pet())
            ->setId($pet->getId())
            ->setName($pet->getName())
            ->setCategoryId($pet->getType())
            // ->setBreedId($pet['UF_BREED']) // toDo: когда доделают справочник пород
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

    /**
     * @return array
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getPetBreeds()
    {
        $breeds = [];
        $dataManager = Application::getHlBlockDataManager('bx.hlblock.petbreed');
        $reference = (new HlbReferenceQuery($dataManager::query()))->exec();
        /**
         * @var $item HlbReferenceItem
         */
        foreach ($reference->getValues() as $item) {
            $breeds[$item->getXmlId()] = [
                'id' => $item->getXmlId(),
                'title' => $item->getName()
            ];
        }
        return $breeds;
    }

    private function customSort($a, $b)
    {
        if (isset($a['sort']) && $a['sort'] != $b['sort']) {
            return $a['sort'] > $b['sort'] ? 1 : -1;
        }
        if ($a['title'] == 'Другое') {
            return 1;
        } elseif ($b['title'] == 'Другое') {
            return -1;
        }
        return strcmp($a['title'], $b['title']);
    }

}
