<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

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
    const VARIANT_DELIMITER = ',';

    use FilterTrait;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        if ($fields) {
            $this
                ->withName($fields['UF_NAME'] ?: $this->getName())
                ->withSort($fields['UF_SORT'] ?: $this->getSort())
                ->withXmlId($fields['UF_CODE'] ?: $this->getXmlId());
        }
    }

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

        if (is_null($rawValue)) {
            return [];
        }

        if (is_string($rawValue) && strpos($rawValue, static::VARIANT_DELIMITER)) {
            return explode(static::VARIANT_DELIMITER, $rawValue);
        }

        if (is_array($rawValue)) {
            return $rawValue;
        }

        return [$rawValue];
    }
}
