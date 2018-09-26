<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Elastica\Aggregation\AbstractAggregation;
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
        if (null === $this->allVariants) {
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
    public function setAvailableVariants(array $availableValues): void
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
     * @throws \InvalidArgumentException
     */
    public function getAvailableVariants(): VariantCollection
    {
        /**
         * @var VariantCollection $fullCollection
         * @var VariantCollection $returnCollection
         */
        $fullCollection = $this->getAllVariants();
        $returnCollection = $fullCollection->filter(
            function (Variant $variant) {
                return $variant->isAvailable();
            }
        );
        $toUnset = [];
        /** @var Variant $variant */
        foreach ($returnCollection as $id => $variant) {
            if ($baseValueId = $variant->getBaseValueId()) {
                if ($returnCollection->containsKey($baseValueId)) {
                    $returnCollection->remove($id);
                } elseif ($toUnset[$baseValueId]) {
                    $returnCollection->remove($id);
                } else {
                    /** @var Variant $baseVariant */
                    $baseVariant = $fullCollection->get($baseValueId);
                    $variant->withValue($baseVariant->getValue());
                    $toUnset[$baseValueId] = true;
                }
            }
        }
        return $returnCollection;
    }

    /**
     * Установить выбранные варианты фильтра
     *
     * @param string[] $checkedValues Массив значений выбранных вариантов.
     *
     * @return void
     */
    public function setCheckedVariants(array $checkedValues): void
    {
        $checkedValuesIndex = array_flip($checkedValues);

        /** @var Variant $variant */
        foreach ($this->getAllVariants() as $variant) {
            $value = $variant->getValue();
            $issetChecked = false;
            if (isset($checkedValuesIndex[$value])) {
                $issetChecked = true;
            } else {
                $values = explode(',', $value);
                foreach ($values as $value) {
                    if(isset($checkedValuesIndex[$value])) {
                        $issetChecked = true;
                        break;
                    }
                }
            }
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
     * @throws \InvalidArgumentException
     */
    public function getAggs(): AggCollection
    {
        return new AggCollection([$this->getAggRule()]);
    }

    /**
     * @return AbstractAggregation
     */
    public function getAggRule(): AbstractAggregation
    {
        return (new Terms($this->getFilterCode()))
            ->setField($this->getRuleCode())
            ->setSize(9999);
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
    public function setVisible(bool $visible): void
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
     * @throws \InvalidArgumentException (sic)
     * @throws UnexpectedValueException
     *
     */
    public function collapse(string $aggName, array $aggResult): void
    {
        // для nested-фильтров
        if ($aggResult[$aggName]) {
            $aggResult = $aggResult[$aggName];
        }

        if (!array_key_exists('buckets', $aggResult) || !\is_array($aggResult['buckets'])) {
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
                $keys = explode(',', $variant->getValue());
                $contains = false;
                $count = 0;
                foreach ($keys as $key) {
                    /** @var Bucket $bucket */
                    if ($bucket = $bucketCollection->get($key)) {
                        $contains = true;
                        $count += $bucket->getDocCount();
                    }
                }

                $variant
                    ->withAvailable($contains)
                    ->withCount($count);

                return $variant;
            }
        );

    }
}
