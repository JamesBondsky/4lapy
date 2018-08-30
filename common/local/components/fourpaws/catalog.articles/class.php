<?php

namespace FourPaws\Components;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\BitrixOrm\Query\ArticleQuery;

/** @noinspection AutoloadingIssuesInspection */
class CatalogArticlesComponent extends FourPawsComponent
{
    /**
     * @param $params
     *
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

        $params['SECTION_ID'] = (int)$params['SECTION_ID'] ?: -1;
        $params['COUNT'] = (int)$params['COUNT'] ?: 4;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function prepareResult(): void
    {
        $this->arResult['ARTICLES_COLLECTION'] = (new ArticleQuery())
            ->withFilter(['IBLOCK_SECTION_ID' => $this->arParams['SECTION_ID']])
            ->withNav(['nTopCount' => $this->arParams['COUNT']])
            ->exec();
    }

}
