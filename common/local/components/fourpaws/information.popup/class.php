<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\AppBundle\Service\FlashMessageService;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsInformationPopupComponent extends FourPawsComponent
{
    /**
     * @var FlashMessageService
     */
    protected $flashService;

    /**
     * FourPawsErrorPopupComponent constructor.
     * @param CBitrixComponent|null $component
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function __construct(?CBitrixComponent $component = null)
    {
        $this->flashService = Application::getInstance()->getContainer()->get('flash.message');
        parent::__construct($component);
    }

    /**
     * @param $params
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TYPE'] = 'N';
        return parent::onPrepareComponentParams($params);
    }

    public function prepareResult(): void
    {
        $this->arResult['ERRORS'] = $this->flashService->get(FlashMessageService::FLASH_TYPE_ERROR);
        $this->arResult['NOTICES'] = $this->flashService->get(FlashMessageService::FLASH_TYPE_NOTICE);
    }
}