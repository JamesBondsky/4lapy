<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Elastica\Aggregation\Terms;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Search\Helper\AggsHelper;
use FourPaws\Search\Model\Bucket;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Trait FilterTrait
 *
 * Это базовая реализация интерфейса FilterInterface , выполненная в виде трейта для того, чтобы можно было подмешать
 * её в \FourPaws\Catalog\Model\Category . В этом трейте **должны быть только те методы**, которые есть в
 * FilterInterface и реализация которых является общей как для \FourPaws\Catalog\Model\Filter\Abstraction\FilterBase,
 * так и для
 * \FourPaws\Catalog\Model\Category . Остальные методы должны быть в соответствующем классе.
 *
 * @see FilterBase
 *
 * @package FourPaws\Catalog\Filter
 */
trait FilterTrait
{
    /**
     * @var VariantCollection
     */
    private $allVariants;

    /**
     * @var bool
     */
    protected $visible = true;

    /**
     * Возвращает все возможные варианты выбора фильтра с учётом динамически установленных состояний выбранности и
     * доступности.
     *
     * @return VariantCollection
     */
    public function getAllVariants(): VariantCollection
    {
        if (is_null($this->allVariants)) {
            /**
             * Храним в свойстве актуальный набор вариантов с состояниями,
             * а то из кеша будет возвращаться неактуальное значение.
             */
            $this->allVariants = $this->doGetAllVariants();
        }

        return $this->allVariants;
    }

    /**
     * Возвращает все возможные варианты выбора фильтра вне зависимости от того, есть под эти варианты результаты или
     * нет. Результат работы этого метода должен кешироваться.
     *
     * @return VariantCollection
     */
    abstract protected function doGetAllVariants(): VariantCollection;

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

        /** @var Variant $variant */
        foreach ($this->getAllVariants() as $variant) {
            $variant->withAvailable(isset($availableValuesIndex[$variant->getValue()]));
        }
    }

    /**
     * Возвращает доступные возможные варианты выбора фильтра с учётом существующих результатов.
     *
     * @return VariantCollection
     */
    public function getAvailableVariants(): VariantCollection
    {
        return $this->getAllVariants()->filter(
            function (Variant $variant) {
                return $variant->isAvailable();
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

        /** @var Variant $variant */
        foreach ($this->getAllVariants() as $variant) {
            $value = $variant->getValue();
            $issetChecked = isset($checkedValuesIndex[$value]);
            $variant->withChecked($issetChecked);
        }
    }

    /**
     * Получить выбранные варианты
     *
     * @return VariantCollection
     */
    public function getCheckedVariants(): VariantCollection
    {
        return $this->getAllVariants()->filter(
            function (Variant $variant) {
                return $variant->isChecked();
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
     * Возвращает аггрегации по фильтру.
     *
     * @return AggCollection
     */
    public function getAggs(): AggCollection
    {
        $termsAgg = (new Terms($this->getFilterCode()))->setField($this->getRuleCode())
                                                       ->setSize(9999);

        return new AggCollection([$termsAgg]);
    }

    /**
     * Проверяет, является ли фильтр видимым на странице.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Устанавливает видимость фильтра на странице.
     *
     * @param bool $visible
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
    }

    /**
     * "Схлопывает" фильтр по его аггрегации.
     *
     * @param string $aggName
     * @param array $aggResult
     *
     * @return void
     * @throws UnexpectedValueException
     *
     */
    public function collapse(string $aggName, array $aggResult)
    {
        if (!array_key_exists('buckets', $aggResult) || !is_array($aggResult['buckets'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Отсутствуют корректные buckets в результате аггрегации `%s`',
                    $aggName
                )
            );
        }

        $bucketCollection = AggsHelper::makeBucketCollection($aggResult['buckets']);

        $this->getAllVariants()->map(
            function (Variant $variant) use ($bucketCollection) {

                if ($bucketCollection->containsKey($variant->getValue())) {

                    /** @var Bucket $bucket */
                    $bucket = $bucketCollection->get($variant->getValue());

                    $variant->withAvailable(true)
                            ->withCount($bucket->getDocCount());

                } else {
                    $variant->withAvailable(false)
                            ->withCount(0);
                }

                return $variant;
            }
        );

    }
}
