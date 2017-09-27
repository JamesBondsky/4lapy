<?php

namespace FourPaws\Catalog\Filter;

interface FilterInterface
{
    /**
     * Возвращает все возможные варианты выбора фильтра вне зависимости от того, есть под эти варианты результаты или
     * нет.
     *
     * @return Variant[]
     */
    public function getAllVariants();

    /**
     * Устанавливает набор доступных к выбору вариантов
     *
     * @param string[] $availableValues
     *
     * @return void
     */
    public function setAvailableVariants(array $availableValues);

    /**
     * Возвращает доступные возможные варианты выбора фильтра с учётом существующих результатов.
     *
     * @return Variant[]
     */
    public function getAvailableVariants();

    /**
     * Установить выбранные варианты фильтра
     *
     * @param string[] $checkedValues Массив значений выбранных вариантов.
     *
     * @return void
     */
    public function setCheckedVariants(array $checkedValues);

    /**
     * Получить выбранные варианты
     *
     * @return Variant[]
     */
    public function getCheckedVariants();

    /**
     * Возвращает true, если у фильтра есть хоть один доступный возможный вариант выбора
     *
     * @return bool
     */
    public function hasAvailableVariants(): bool;

    /**
     * Возвращает true, если у фильтра есть хоть один доступный возможный вариант выбора
     *
     * @return bool
     */
    public function hasCheckedVariants(): bool;

    public function getFilterRule(): array;

    public function getAggRule(): array;


}
