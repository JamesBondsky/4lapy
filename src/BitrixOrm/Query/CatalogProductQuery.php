<?php

namespace FourPaws\BitrixOrm\Query;


use Bitrix\Main\DB\Result;
use CDBResult;
use FourPaws\BitrixOrm\Collection\CollectionBase;

class CatalogProductQuery extends QueryBase
{

    /**
     * Исполняет запрос и возвращает коллекцию сущностей. Например, элементов инфоблока.
     *
     * @return CollectionBase
     */
    public function exec(): CollectionBase
    {
        // TODO: Implement exec() method.
    }

    /**
     * Непосредственное выполнение запроса через API Битрикса
     *
     * @return mixed|CDBResult|Result
     */
    public function doExec()
    {
        return \CCatalogProduct::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $this->getGroup() ?: false,
            $this->getNav() ?: false,
            $this->getSelectWithBase());
    }

    /**
     * Возвращает базовый фильтр - та его часть, которую нельзя изменить. Например, ID инфоблока.
     *
     * @return array
     */
    public function getBaseFilter(): array
    {
        return [];
    }

    /**
     * Возвращает базовую выборку полей. Например, те поля, которые обязательно нужны для создания сущности.
     *
     * @return array
     */
    public function getBaseSelect(): array
    {
        return [
            'ID',
            'QUANTITY',
            'WEIGHT',
            'WIDTH',
            'LENGTH',
            'HEIGHT',
            'ELEMENT_IBLOCK_ID',
            'ELEMENT_XML_ID',
            'ELEMENT_NAME',
        ];
    }
}