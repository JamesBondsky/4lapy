<?php

namespace FourPaws\Catalog\Model\Filter;

use Elastica\Query\AbstractQuery;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use LogicException;

/**
 * Class InternalFilter
 *
 * Внутренний фильтр. Используется для того, чтобы задать такие базовые условия фильтрации, от которых нельзя
 * отказаться: активность по флагу и дате для товара, оффера, бренда, текущий выбранный регион и т.п.
 *
 * Внутренний фильтр всегда невидимый: его нельзя показать никогда. У внутреннего фильтра нет вариантов и с ним нельзя
 * взаимодействовать и такой фильтр всегда активный. Также у такого фильтра некоторые параметры могут задаваться
 * динамически, чтобы легко собирать нужные комбинации.
 *
 * @package FourPaws\Catalog\Model\Filter
 */
class InternalFilter extends FilterBase
{

    /**
     * @var string
     */
    protected $filterCode = '';

    /**
     * @var AbstractQuery
     */
    protected $filterRule;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        throw new LogicException('Внутренний фильтр следует инстанцировать, используя метод create.');
    }

    /**
     * @param string $filterCode
     * @param AbstractQuery $filterRule
     *
     * @return InternalFilter
     */
    public static function create(string $filterCode, AbstractQuery $filterRule): InternalFilter
    {
        return (new self())->withFilterCode($filterCode)
                           ->withFilterRule($filterRule);
    }

    /**
     * @return AbstractQuery
     */
    public function getFilterRule(): AbstractQuery
    {
        return $this->filterRule;
    }

    /**
     * @param AbstractQuery $filterRule
     *
     * @return $this
     */
    public function withFilterRule(AbstractQuery $filterRule)
    {
        $this->filterRule = $filterRule;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilterCode(): string
    {
        return $this->filterCode;
    }

    /**
     * @param mixed $filterCode
     *
     * @return $this
     */
    public function withFilterCode($filterCode)
    {
        $this->filterCode = $filterCode;

        return $this;
    }

    /**
     * У внутреннего фильтра не может быть кода поля.
     *
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return '';
    }

    /**
     * У внутреннего фильтра не может быть кода свойства.
     *
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * У внутреннего фильтра не может быть вариантов.
     *
     * @inheritdoc
     */
    protected function doGetAllVariants(): VariantCollection
    {
        return new VariantCollection();
    }

    /**
     * Внутренний фильтр всегда скрыт.
     *
     * @inheritdoc
     */
    public function isVisible(): bool
    {
        return false;
    }

    /**
     * Внутренний фильтр всегда активен.
     *
     * @inheritdoc
     */
    public function hasCheckedVariants(): bool
    {
        return true;
    }

    /**
     * У внутреннего фильтра не может быть аггрегаций.
     *
     * @inheritdoc
     */
    public function getAggs(): AggCollection
    {
        return new AggCollection();
    }

}
