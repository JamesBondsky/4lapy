<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetPetsComponent extends CBitrixComponent
{
    /**
     * @var PetService
     */
    private $petService;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    private $hlSizeSelection;
    private $hlSize;

    // тип питомца собаки
    const DOG_TYPE = 'sobaki';

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
        $this->petService = $container->get('pet.service');
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->hlSizeSelection = $container->get('bx.hlblock.clothingsizeselection');
        $this->hlSize = $container->get('bx.hlblock.clothingsize');
    }

    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 360000;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws \Exception
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
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return null;
        }

        $this->setFrameMode(true);

        if ($this->startResultCache($this->arParams['CACHE_TIME'], ['user_id' => $this->currentUserProvider->getCurrentUserId()])) {
            TaggedCacheHelper::addManagedCacheTags([
                'personal:pets:' . $this->currentUserProvider->getCurrentUserId(),
                'hlb:field:pets_user:' . $this->currentUserProvider->getCurrentUserId()
            ]);

            $this->arResult['ITEMS'] = $this->petService->getCurUserPets();

            /** получение пола */
            $this->setGenderVals();

            /** получение типов питомцев */
            $this->setPetTypes();

            /** получение размеров питомца */
            $this->setPetSizes();

            /** получение размеров для определения размера */
            $this->setSizesForJs();

            $this->includeComponentTemplate();
        }

        return true;
    }

    /**
     * @throws ArgumentException
     * @throws LoaderException
     */
    private function setGenderVals(): void
    {
        $this->arResult['GENDER'] = [];
        $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
            [
                'FIELD_NAME' => 'UF_GENDER',
                'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName('Pet'),
            ]
        )->exec()->fetch()['ID'];
        $userFieldEnum = new \CUserFieldEnum();
        $res = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $userFieldId]);
        while ($item = $res->Fetch()) {
            $this->arResult['GENDER'][$item['XML_ID']] = $item;
        }
    }

    /**
     * @throws \Exception
     */
    private function setPetTypes(): void
    {
        $this->arResult['PET_TYPES'] = [];
        $res =
            HLBlockFactory::createTableObject(Pet::PET_TYPE)::query()->setFilter(['UF_USE_BY_PET' => 1])->setSelect(
                [
                    'ID',
                    'UF_NAME',
                    'UF_CODE'
                ]
            )->setOrder(['UF_SORT' => 'asc'])->exec();
        while ($item = $res->fetch()) {
            $this->arResult['PET_TYPES'][] = $item;
        }
    }

    /**
     * @throws \Exception
     */
    private function setPetSizes(): void
    {
        $this->arResult['PET_SIZES'] = [];
        $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
            [
                'FIELD_NAME' => 'UF_SIZE',
                'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName('Pet'),
            ]
        )->exec()->fetch()['ID'];
        $userFieldEnum = new \CUserFieldEnum();
        $res = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $userFieldId]);
        while ($item = $res->Fetch()) {
            if($item['XML_ID'] == 'n'){
                continue;
            }

            $this->arResult['PET_SIZES'][$item['XML_ID']] = $item;
        }
    }

    /**
     * @throws \Exception
     */
    private function setSizesForJs(): void
    {
        $sizes = $this->hlSizeSelection::query()->setSelect(['*', 'UF_*'])->exec()->fetchAll();
        $sizeInfo = [];

        $dbres = $this->hlSize::query()->setSelect(['*', 'UF_*'])->exec();
        while($size = $dbres->fetch()){
            $sizeInfo[$size['UF_NAME']] = $size;
        }

        foreach ($sizes as $size) {
            $this->arResult['JS_SIZES'][] = [
                'name'      => $size['UF_CODE'],
                'back_min'  => $size['UF_BACK_MIN'],
                'back_max'  => $size['UF_BACK_MAX'],
                'chest_min' => $size['UF_CHEST_MIN'],
                'chest_max' => $size['UF_CHEST_MAX'],
                'neck_min'  => $size['UF_NECK_MIN'],
                'neck_max'  => $size['UF_NECK_MAX'],
                'code'      => $sizeInfo[$size['UF_CODE']]['UF_XML_ID']
            ];
        }
    }
}
