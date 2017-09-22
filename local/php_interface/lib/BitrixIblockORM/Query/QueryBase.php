<?php

namespace FourPaws\BitrixIblockORM\Query;

use CDBResult;
use FourPaws\BitrixIblockORM\Collection\CollectionBase;

abstract class QueryBase
{
    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $filter = [];

    /**
     * @var array
     */
    protected $group = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var array
     */
    protected $nav = [];

    public function __construct()
    {
        /**
         * По умолчанию следует выбирать активные и доступные элементы.
         * При необходимости для конкретного Query можно просто вызвать withFilter([]), чтобы выбрать всё.
         */
        $this->withFilter(self::getActiveAccessableElementsFilter());
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @param array $select
     *
     * @return $this
     */
    public function withSelect(array $select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     *
     * @return $this
     */
    public function withFilter(array $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return $this
     */
    public function withFilterParameter(string $name, $value)
    {
        $name = trim($name);

        if ($name != '') {
            $this->filter[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withoutFilterParameter(string $name)
    {
        $name = trim($name);

        if (isset($this->filter[$name])) {
            unset($this->filter[$name]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getGroup(): array
    {
        return $this->group;
    }

    /**
     * @param array $group
     *
     * @return $this
     */
    public function withGroup(array $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @param array $order
     *
     * @return $this
     */
    public function withOrder(array $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     */
    public function getNav(): array
    {
        return $this->nav;
    }

    /**
     * @param array $nav
     *
     * @return $this
     */
    public function withNav(array $nav)
    {
        $this->nav = $nav;

        return $this;
    }

    /**
     * Исполняет запрос и возвращает коллекцию сущностей. Например, элементов инфоблока.
     *
     * @return CollectionBase
     */
    abstract public function exec();

    /**
     * Непосредственное выполнение запроса через API Битрикса
     *
     * @return CDBResult
     */
    abstract public function doExec(): CDBResult;

    /**
     * Возвращает базовый фильтр - та его часть, которую нельзя изменить. Например, ID инфоблока.
     *
     * @return array
     */
    abstract public function getBaseFilter(): array;

    /**
     * Возвращает базовую выборку полей. Например, те поля, которые обязательно нужны для создания сущности.
     *
     * @return array
     */
    abstract public function getBaseSelect(): array;

    public function getFilterWithBase(): array
    {
        return array_merge($this->getFilter(), $this->getBaseFilter());
    }

    public function getSelectWithBase()
    {
        return array_unique(array_merge($this->getSelect(), $this->getBaseSelect()));
    }

    /**
     * Возвращает фильтр активных и доступных элементов инфоблока.
     *
     * Это базовая основа и в публичной части всегда рекомендуется использовать такой фильтр, чтобы можно было всегда
     * управлять доступами, а также флажком и датами активности.
     *
     * @return array
     */
    public static function getActiveAccessableElementsFilter(): array
    {
        return [
            'CHECK_PERMISSIONS' => 'Y',
            'ACTIVE'            => 'Y',
            'ACTIVE_DATE'       => 'Y',
        ];
    }
}
