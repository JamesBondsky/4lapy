<?php

namespace FourPaws\Catalog\Filter;

use FourPaws\Catalog\Model\Category;

/**
 * Trait FilterTrait is a basic implementation of FilterInterface
 * @package FourPaws\Catalog\Filters
 */
trait FilterTrait
{
    /**
     * @var Variant[]
     */
    private $allVariants = [];

    /**
     * @var Category
     */
    private $category;

    /**
     * Запрашивает все возможные варианты выбора фильтра без зависимости от доступности для конкретного фильтра.
     * @internal Результат работы метода должен кешироваться.
     *
     * @return Variant[]
     */
    abstract protected function doGetAllVariants();

    /**
     * Возвращает все возможные варианты выбора фильтра вне зависимости от того, есть под эти варианты результаты или
     * нет.
     *
     * @return Variant[]
     */
    public function getAllVariants()
    {
        if (count($this->allVariants) == 0) {
            $this->allVariants = $this->doGetAllVariants();
        }

        return $this->allVariants;
    }

    /**
     * Устанавливает набор доступных к выбору вариантов
     *
     * @param string[] $availableValues
     *
     * @return void
     */
    public function setAvailableVariants(array $availableValues)
    {
        $availableValuesIndex = array_flip($availableValues);

        foreach ($this->getAllVariants() as $variant) {
            if ($variant instanceof Variant) {
                $variant->withAvailable(isset($availableValuesIndex[$variant->getValue()]));
            }
        }
    }

    /**
     * Возвращает доступные возможные варианты выбора фильтра с учётом существующих результатов.
     *
     * @return Variant[]
     */
    public function getAvailableVariants()
    {
        return array_filter(
            $this->getAllVariants(),
            function ($variant) {
                return $variant instanceof Variant && $variant->isAvailable();
            }
        );

    }

    /**
     * Установить выбранные варианты фильтра
     *
     * @param string[] $checkedValues Массив значений выбранных вариантов.
     *
     * @return void
     */
    public function setCheckedVariants(array $checkedValues)
    {
        $checkedValuesIndex = array_flip($checkedValues);

        foreach ($this->getAllVariants() as $variant) {
            if ($variant instanceof Variant) {
                $variant->withChecked(isset($checkedValuesIndex[$variant->getValue()]));
            }
        }
    }

    /**
     * Получить выбранные варианты
     *
     * @return Variant[]
     */
    public function getCheckedVariants()
    {
        return array_filter(
            $this->getAllVariants(),
            function ($variant) {
                return $variant instanceof Variant && $variant->isChecked();
            }
        );
    }

    /**
     * Возвращает true, если у фильтра есть хоть один доступный возможный вариант выбора
     *
     * @return bool
     */
    public function hasCheckedVariants(): bool
    {
        foreach ($this->getAllVariants() as $variant) {
            if ($variant->isChecked()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает true, если у фильтра есть хоть один доступный возможный вариант выбора
     *
     * @return bool
     */
    public function hasAvailableVariants(): bool
    {
        foreach ($this->getAllVariants() as $variant) {
            if ($variant->isAvailable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function withCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

}
