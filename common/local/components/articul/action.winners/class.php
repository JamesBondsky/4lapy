<?php

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;

/** @noinspection AutoloadingIssuesInspection */

class ActionWinners extends CBitrixComponent
{
    protected $iblockId;

    protected $actionSectionId;

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        if (!$arParams['IBLOCK_TYPE'] || !$arParams['IBLOCK_CODE']) {
            $arParams['IBLOCK_TYPE'] = 'grandin';
            $arParams['IBLOCK_CODE'] = 'action_winners';
        }
        if (!$arParams['SECTION_CODE']) {
            throw new InvalidArgumentException('PARAMETER SECTION_CODE IS REQUIRED');
        }
        if (!$arParams['CACHE_TIME']) {
            $arParams['CACHE_TIME'] = 3600 * 24;
        }
        if (!$arParams['CACHE_TYPE']) {
            $arParams['CACHE_TYPE'] = 'A';
        }

        return $arParams;
    }

    /**
     * @return array
     */
    protected function getWinners(): array
    {
        $dbSections = CIBlockSection::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], ['IBLOCK_ID' => $this->iblockId, 'SECTION_ID' => $this->actionSectionId, 'ACTIVE' => 'Y']);
        $arSections = [];
        $arSectionsMap = [];
        while ($arSection = $dbSections->Fetch()) {
            $arSections[] = [
                'DATE' => $arSection['NAME'],
                'WINNERS' => []
            ];
            $arSectionsMap[$arSection['ID']] = count($arSections) - 1;
        }

        if (!empty($arSectionsMap)) {
            $elements = CIBlockElement::GetList(
                [
                    'SORT' => 'ASC'
                ],
                [
                    'IBLOCK_ID' => $this->iblockId,
                    'IBLOCK_SECTION_ID' => array_keys($arSectionsMap)
                ],
                false,
                false,
                [
                    'NAME',
                    'CODE',
                    'PREVIEW_PICTURE',
                    'IBLOCK_SECTION_ID',
                    'PROPERTY_PHONE',
                    'PROPERTY_ICON',
                    'PROPERTY_SLIDER',
                ]
            );
            
            while ($element = $elements->Fetch()) {
                $element['PREVIEW_PICTURE'] = $img_path = \CFile::GetPath($element['PREVIEW_PICTURE']);
                $arSections[$arSectionsMap[$element['IBLOCK_SECTION_ID']]]['WINNERS'][] = $element;
            }
        }

        return $arSections;
    }

    /**
     * @return mixed|void
     * @throws IblockNotFoundException
     */
    public function executeComponent()
    {
        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            $this->iblockId = IblockUtils::getIblockId($this->arParams['IBLOCK_TYPE'], $this->arParams['IBLOCK_CODE']);
            $section = CIBlockSection::GetList([], ['IBLOCK_ID' => $this->iblockId, 'CODE' => $this->arParams['SECTION_CODE']], false, ['ID'])->Fetch();
            if (!$section) {
                throw new InvalidArgumentException('Invalid Section');
            }
            $this->actionSectionId = $section['ID'];
            $this->arResult = $this->getWinners();
            $this->includeComponentTemplate();
        }
    }
}
