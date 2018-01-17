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
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetPetsComponent extends CBitrixComponent
{
    /**
     * @var PetService
     */
    private $petService;
    
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
    }
    
    /**
     * {@inheritdoc}
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
        $this->setFrameMode(true);
        
        if ($this->startResultCache()) {
            $this->arResult['ITEMS'] = $this->petService->getCurUserPets();
            
            /** получение пола */
            $this->setGenderVals();
            
            /** получение типов питомцев */
            $this->setPetTypes();
    
            $this->includeComponentTemplate();
        }
        
        return true;
    }
    
    /**
     * @throws ArgumentException
     * @throws LoaderException
     */
    private function setGenderVals()
    {
        $this->arResult['GENDER'] = [];
        $userFieldId              = UserFieldTable::query()->setSelect(['ID'])->setFilter(
            [
                'FIELD_NAME' => 'UF_GENDER',
                'ENTITY_ID'  => 'HLBLOCK_' . HighloadHelper::getIdByName('Pet'),
            ]
        )->exec()->fetch()['ID'];
        $userFieldEnum            = new \CUserFieldEnum();
        $res                      = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $userFieldId]);
        while ($item = $res->Fetch()) {
            $this->arResult['GENDER'][$item['XML_ID']] = $item;
        }
    }
    
    /**
     * @throws \Exception
     */
    private function setPetTypes()
    {
        $this->arResult['PET_TYPES'] = [];
        $res                         = HLBlockFactory::createTableObject(Pet::PET_TYPE)::query()->setSelect(
            [
                'ID',
                'UF_NAME',
            ]
        )->setOrder(['UF_SORT' => 'asc'])->exec();
        while ($item = $res->fetch()) {
            $this->arResult['PET_TYPES'][] = $item;
        }
    }
}
