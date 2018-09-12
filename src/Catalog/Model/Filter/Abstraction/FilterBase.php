<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Terms as AggTerms;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Terms;
use FourPaws\BitrixOrm\Model\HlbItemBase;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Variant;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FilterBase
 * @package FourPaws\Catalog\Filter
 *
 * В этом классе должны быть описаны методы, которые подходят всем обычным фильтрам, но не подходят к
 *     \FourPaws\Catalog\Model\Category
 *
 * @see     FilterTrait
 *
 */
abstract class FilterBase extends HlbItemBase implements FilterInterface
{
    /**
     * Знак разделения множественных значений фильтра
     */
    public const VARIANT_DELIMITER = ',';

    use FilterTrait;
    /**
     * @var bool
     */
    protected $UF_ACTIVE = true;
    /**
     * @var bool
     */
    protected $UF_SHOW_WITH_PICTURE = false;

    /**
     * @var bool
     */
    protected $UF_HIDE_IN_FILTER = false;

    /**
     * @var bool
     */
    protected $UF_MERGE_VALUES = false;

    /**
     * FilterBase constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        if (isset($fields['UF_ACTIVE'])) {
            if (is_numeric($fields['UF_ACTIVE'])) {
                $fields['UF_ACTIVE'] = (bool)$fields['UF_ACTIVE'];
            } else {
                $fields['UF_ACTIVE'] = BitrixUtils::bitrixBool2bool($fields['UF_ACTIVE']);
            }
        }
        parent::__construct($fields);

    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->UF_ACTIVE;
    }

    /**
     * @inheritdoc
     */
    public function withActive(bool $active)
    {
        $this->UF_ACTIVE = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowWithPicture(): bool
    {
        return (bool)$this->UF_SHOW_WITH_PICTURE;
    }

    /**
     * @param bool $isShowWithPicture
     *
     * @return $this
     */
    public function withShowWithPicture(bool $isShowWithPicture)
    {
        $this->UF_SHOW_WITH_PICTURE = $isShowWithPicture;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMergeValues(): bool
    {
        return (bool)$this->UF_MERGE_VALUES;
    }

    /**
     * @param bool $isMergeValues
     *
     * @return $this
     */
    public function withMergeValues(bool $isMergeValues)
    {
        $this->UF_MERGE_VALUES = $isMergeValues;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHideInFilter(): bool
    {
        return (bool)$this->UF_HIDE_IN_FILTER;
    }

    /**
     * @param bool $hide
     *
     * @return $this
     */
    public function withHideInFilter(bool $hide)
    {
        $this->UF_HIDE_IN_FILTER = $hide;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        if (\is_bool($array['UF_ACTIVE'])) {
            $array['UF_ACTIVE'] = BitrixUtils::bool2BitrixBool($array['UF_ACTIVE']);
        }
        return $array;
    }

    /**
     * @return AbstractAggregation
     */
    public function getAggRule(): AbstractAggregation
    {
        return (new AggTerms($this->getFilterCode()))
            ->setField($this->getRuleCode())
            ->setSize(9999);
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
        $checkedValues = array_map(
            function (Variant $variant) {
                return $variant->getValue();
            },
            $this->getCheckedVariants()->toArray()
        );

        return new Terms($this->getRuleCode(), array_values($checkedValues));
    }

    /**
     * @param Request $request
     */
    public function initState(Request $request)
    {
        $this->setCheckedVariants($this->getCheckedValues($request));
    }

    /**
     * Возвращает отмеченные значения по информации из запроса.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getCheckedValues(Request $request): array
    {
        $rawValue = $request->get($this->getFilterCode());

        if (null === $rawValue) {
            return [];
        }

        if (\is_string($rawValue) && strpos($rawValue, static::VARIANT_DELIMITER)) {
            return explode(static::VARIANT_DELIMITER, $rawValue);
        }

        if (\is_array($rawValue)) {
            return $rawValue;
        }

        return [$rawValue];
    }
}
