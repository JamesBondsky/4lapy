<?php

use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\SystemException;

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
        if ($obCache->InitCache(36000, $this->arParams['ELEMENT_CODE'], "/iblock/menu")) {
            $share = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $share = CIBlockElement::GetList([], ['CODE' => $this->arParams['ELEMENT_CODE']])->Fetch();
            if ($share) {
                $share['DETAIL_PICTURE'] = CFile::GetByID($share['DETAIL_PICTURE'])->Fetch();
                $obCache->EndDataCache($share);
            }
        }

        if (!$share) {
            Bitrix\Iblock\Component\Tools::process404(
                trim($this->arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_DETAIL_NF")
                , true
                , true
                , true
                , $this->arParams["FILE_404"]
            );
        }

        $activeTo = new DateTime($share['ACTIVE_TO']);
        $currentDate = new DateTime();


        if (($activeTo && $activeTo < $currentDate && $share['ACTIVE_TO']) || $share['ACTIVE'] != 'Y') {
            if (isset($arParams['URL_REDIRECT_404'])) {
                LocalRedirect($this->arParams['URL_REDIRECT_404']);
                return;
            }
        }

        $this->arResult = $share;
        $this->includeComponentTemplate();
        return $share['ID'];
    }
}