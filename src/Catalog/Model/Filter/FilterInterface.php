<?php

namespace FourPaws\Catalog\Model\Filter;

use Elastica\Query\AbstractQuery;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use Symfony\Component\HttpFoundation\Request;

interface FilterInterface
{
    /**
     * Возвращает символьный код фильтра.
     *
     * По коду фильтра производится поиск его значения в HTTP-запросе.
     *
     * @return string
     */
    public function getFilterCode(): string;

    /**
     * Возвращает код свойства инфоблока, с которым связан фильтр. Пустая строка, если фильтр не связан ни с одним
     * свойством.
     *
     * Используется для того, чтобы в зависимости от настроек "Умного фильтра" показывать или скрывать фильтр в любой
     * категории каталога.
     *
     * @return string
     */
    public function getPropCode(): string;

    /**
     * Возвращает код поля, по которому на самом деле идёт фильтрация.
     *
     * Используется для формирования условия фильтрации для обращения в Elasticsearch.
     *
     * @return string
     *
     * @see getFilterRule()
     */
    public function getRuleCode(): string;

    /**
     * Возвращает все возможные варианты выбора фильтра с учётом динамически установленных состояний выбранности и
     * доступности.
     *
     * @return VariantCollection
     */
    public function getAllVariants(): VariantCollection;

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
     * @return VariantCollection
     */
    public function getAvailableVariants(): VariantCollection;

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
     * @return VariantCollection
     */
    public function getCheckedVariants(): VariantCollection;

    /**
     * Возвращает true, если у фильтра есть хоть один доступный возможный вариант выбора
     *
     * @return bool
     */
    public function hasAvailableVariants(): bool;

    /**
     * Возвращает true, если в фильтре выбрано хотя бы одно значение.
     *
     * @return bool
     *
     */
    public function hasCheckedVariants(): bool;

    /**
     * Возвращает правило фильтрации по выбранным значениям фильтра
     *
     * @return AbstractQuery
     */
    public function getFilterRule(): AbstractQuery;

    /**
     * Возвращает правило аггрегации по фильтру.
     *
     * @return AggCollection
     */
    public function getAggs(): AggCollection;

    /**
     * Проверяет, является ли фильтр видимым на странице.
     *
     * @return bool
     */
    public function isVisible(): bool;

    /**
     * Устанавливает видимость фильтра на странице.
     *
     * @param bool $visible
     *
     * @return void
     */
    public function setVisible(bool $visible);

    /**
     * Инициализирует состояние фильтра по информации из запроса.
     *
     * @param Request $request
     *
     * @return void
     */
    public function initState(Request $request);

}
