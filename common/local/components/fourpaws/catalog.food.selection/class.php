<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FoodSelectionBundle\Service\FoodSelectionService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class CFourPawsFoodSelectionComponent extends CBitrixComponent
{
    /** @var FoodSelectionService $foodSelectionService */
    private $foodSelectionService;
    
    /**
     * CFourPawsFoodSelectionComponent constructor.
     *
     * @param \CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
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
        
        $this->foodSelectionService = $container->get('food_selection.service');
    }

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TIME'] = 360000;
        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            $this->arResult['PET_TYPES'] = $this->foodSelectionService->getSectionsByParentSectionId(
                    $this->foodSelectionService->getSectionIdByXmlId('pet_type', 1)
                );
            $this->includeComponentTemplate();
        }
        
        return true;
    }
}
