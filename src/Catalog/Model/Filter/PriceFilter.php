<?php

namespace FourPaws\Catalog\Model\Filter;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Nested as NestedAggregation;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Nested;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\RangeFilterBase;
use FourPaws\Location\LocationService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use WebArch\BitrixCache\BitrixCache;

class PriceFilter extends RangeFilterBase
{
    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * PriceFilter constructor.
     *
     * @param array $fields
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Price';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'offers.prices.PRICE';
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
        return (new Nested())->setPath('offers.prices')
                             ->setQuery(parent::getFilterRule());

    }

    /**
     * @inheritdoc
     */
    public function getAggs(): AggCollection
    {
        return parent::getAggs()->map(
            function (AbstractAggregation $aggregation) {

                $nested = (new NestedAggregation($aggregation->getName() . 'Nested', 'offers.prices'));
                $nested->addAggregation($aggregation);

                return $nested;
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function collapse(string $aggName, array $aggResult)
    {
        foreach ([$this->getMinFilterCode(), $this->getMaxFilterCode()] as $subAggName) {
            if (
                array_key_exists($subAggName, $aggResult)
                && is_array($aggResult[$subAggName])
            ) {
                parent::collapse($subAggName, $aggResult[$subAggName]);
            }
        }

    }

    /**
     * @inheritdoc
     */
    protected function getRange(): array
    {
        $callDoGetRange = function () {
            return $this->doGetRange();
        };

        $currentRegionCode = $this->locationService->getCurrentRegionCode();

        return (new BitrixCache())->withId(__METHOD__ . ':regId_' . $currentRegionCode)
                                  ->resultOf($callDoGetRange);
    }

}
