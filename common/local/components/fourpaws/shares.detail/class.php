<?php

use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\SystemException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\App\Application as App;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsOrderShopListComponent extends CBitrixComponent
{
    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
    }

    public function executeComponent()
    {
        $obCache = new CPHPCache();

        /** @var LocationService $locationService */
        $locationService = App::getInstance()->getContainer()->get('location.service');
        $regionCode = $locationService->getCurrentRegionCode();

        if ($obCache->InitCache(36000, $this->arParams['ELEMENT_CODE'].$regionCode, "/iblock/menu")) {
            $share = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $share = CIBlockElement::GetList([], ['IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'CODE' => $this->arParams['ELEMENT_CODE']])->Fetch();
            if ($share) {
                $share['DETAIL_PICTURE'] = CFile::GetByID($share['DETAIL_PICTURE'])->Fetch();
                $obCache->EndDataCache($share);
            }
        }

        if (!$share) {
            $share['ERROR'] = true;
            $this->arParams['arParams']['SHOW_PRODUCTS_SALE'] = 'N';
            $this->arParams['arParams']['USE_SHARE'] = 'N';
        } else {
            $activeTo = new DateTime($share['ACTIVE_TO']);
            $currentDate = new DateTime();

            if ((($activeTo && $activeTo < $currentDate && $share['ACTIVE_TO']) || $share['ACTIVE'] != 'Y') && !$share['ERROR']) {
                if (isset($this->arParams['URL_REDIRECT_404'])) {
                    LocalRedirect($this->arParams['URL_REDIRECT_404']);
                    return;
                }
            }

            // запрет просмотра акции из другого региона
            $dbres = CIBlockElement::GetProperty($this->arParams['IBLOCK_ID'], $share['ID'], $by=false, $order=false, ['CODE' => 'REGION']);
            $shareRegions = [];
            while($row = $dbres->fetch()){
                if($row['VALUE'])
                    $shareRegions[] = $row['VALUE'];
            }
            if(!empty($shareRegions) && !in_array($regionCode, $shareRegions)){
                if (isset($this->arParams['URL_REDIRECT_404'])) {
                    LocalRedirect($this->arParams['URL_REDIRECT_404']);
                    return;
                }
            }

            $this->arResult = $share;
        }


        $this->includeComponentTemplate();
        return $share['ID'];
    }
}