<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/** @noinspection AutoloadingIssuesInspection */

class Leto20202QuestionsComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 86400;
        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {

            $this->arResult = [];
            $res = ElementTable::query()
                ->setOrder(['SORT' => 'ASC'])
                ->setFilter([
                    '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::JANUARY),
                    '=ACTIVE' => BaseEntity::BITRIX_TRUE
                ])
                ->setSelect(['NAME', 'DETAIL_TEXT'])
                ->exec();

            while ($element = $res->fetch()) {
                $this->arResult[] = [
                    'name' => $element['NAME'],
                    'text' => $element['DETAIL_TEXT'],
                ];
            }

            $this->includeComponentTemplate();
        }
    }
}
