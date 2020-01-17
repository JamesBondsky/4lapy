<?php

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection AutoloadingIssuesInspection */

class ModifiedSliderComponent extends CBitrixComponent
{
    protected $items = [];

    protected $files = [];

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? getenv('GLOBAL_CACHE_TTL');
        return $params;
    }

    /**
     * @return mixed|void
     * @throws IblockNotFoundException
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->fillItems();
            $this->arResult = [
                'items' => $this->items,
                'files' => $this->files,
            ];

            $this->includeComponentTemplate();
        }
    }

    /**
     * @throws IblockNotFoundException
     */
    protected function fillItems(): void
    {
        $fileIds = [];

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SLIDER_CONTROLLED),
            'ACTIVE' => BaseEntity::BITRIX_TRUE,
            'GLOBAL_ACTIVE' => BaseEntity::BITRIX_TRUE,
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'EXTERNAL_ID',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'PROPERTY_COLOR',
            'PROPERTY_BIG_TEXT',
            'PROPERTY_LINK',
            'PROPERTY_BUTTON_TEXT',
            'PROPERTY_BUTTON_COLOR',
            'PROPERTY_HIDE_LOGO_MOBILE',
            'PROPERTY_LEFT_SVG',
            'PROPERTY_HASH_LEFT_COLOR',
            'PROPERTY_LOCATION',
        ];

        $rsElement = CIBlockElement::GetList(['SORT' => 'ASC'], $arFilter, false, false, $arSelect);

        while ($arElement = $rsElement->Fetch()) {
            if (($arElement['PROPERTY_LOCATION_VALUE'] !== null) && !empty($this->arParams['LOCATION']) && ($arElement['PROPERTY_LOCATION_VALUE'] !== $this->arParams['LOCATION'])) {
                continue;
            }

            $additionalClasses = [];

            if ($arElement['PROPERTY_COLOR_VALUE_XML_ID'] === 'dark') { //todo
                $additionalClasses[] = 'b-promo-banner-item--dark';
            }
            if ($arElement['PROPERTY_BIG_TEXT_VALUE'] === true) {
                $additionalClasses[] = 'b-promo-banner-item--big-text';
            }

            $additionalClasses = ' ' . implode(' ', $additionalClasses);

            $this->items[$arElement['ID']] = [
                'externalId' => $arElement['XML_ID'],
                'previewText' => $arElement['PREVIEW_TEXT'],
                'previewImg' => $arElement['PREVIEW_PICTURE'],
                'link' => $arElement['PROPERTY_LINK_VALUE'],
                'buttonColor' => $arElement['PROPERTY_BUTTON_COLOR_VALUE'],
                'buttonText' => $arElement['PROPERTY_BUTTON_TEXT_VALUE'],
                'hideLogoMobile' => $arElement['PROPERTY_HIDE_LOGO_MOBILE_VALUE'],
                'hashLeftColor' => $arElement['PROPERTY_HASH_LEFT_COLOR_VALUE'],
                'leftColor' => (!empty($arElement['PROPERTY_HASH_LEFT_COLOR_VALUE'])) ? substr($arElement['PROPERTY_HASH_LEFT_COLOR_VALUE'], 1) : false,
                'leftSvg' => $arElement['PROPERTY_LEFT_SVG_VALUE'],
                'additionalClasses' => $additionalClasses,
            ];

            if ($arElement['PREVIEW_PICTURE']) {
                $fileIds[] = $arElement['PREVIEW_PICTURE'];
            }

            if ($arElement['PROPERTY_LEFT_SVG_VALUE'] && !empty($arElement['PROPERTY_HASH_LEFT_COLOR_VALUE'])) {
                $fileIds[] = $arElement['PROPERTY_LEFT_SVG_VALUE'];
            }
        }

        $this->fillFiles($fileIds);
    }

    /**
     * @param $fileIds
     */
    protected function fillFiles($fileIds): void
    {
        if (empty($fileIds)) {
            return;
        }

        $rsFile = CFile::GetList(false, ['@ID' => array_unique($fileIds)]);
        while ($arFile = $rsFile->Fetch()) {
            $this->files[$arFile['ID']] = CFile::GetFileSRC($arFile);
        }
    }
}
