<?php

namespace FourPaws\Components;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsClothingSizeSelection extends FourPawsComponent
{

    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * FourPawsClothingSizeSelection constructor.
     * @param CBitrixComponent|null $component
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function __construct(?CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.clothingsizeselection');

    }

    /**
     * @param $params
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        if (!isset($params['CACHE_TYPE'])) {
            $params['CACHE_TYPE'] = 'A';
        }

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function prepareResult(): void
    {
        $this->arResult['ITEMS'] = $this->dataManager::query()->setSelect(['*', 'UF_*'])->exec()->fetchAll();
    }

}
