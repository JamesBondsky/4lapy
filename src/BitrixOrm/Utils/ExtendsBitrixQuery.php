<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.02.18
 * Time: 17:52
 */

namespace FourPaws\BitrixOrm\Utils;


use Bitrix\Main\Entity\Query;

class ExtendsBitrixQuery extends Query
{
    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    /** @inheritdoc */
    public function __construct($source)
    {
        /** reInit Query*/
        if ($source instanceof Query) {
            $this->entity = clone $source->getEntity();

            /** clear */
            $this->filterHandler = static::filter();
            $this->whereHandler = static::filter();
            $this->havingHandler = static::filter();

            /** set */
            $this->setFilter($source->getFilter());
            $this->setOrder($source->getOrder());
            $this->setLimit($source->getLimit());
        }
        $this->buildBaseQuery();
    }

    /**
     * @return string
     */
    public function getBuildWhere(): string
    {
        $where = $this->query_build_parts['WHERE'];

        return !empty($where) ? ' WHERE ' . $where : '';
    }

    /**
     * @return string
     */
    public function getBuildOrder(): string
    {
        $order = $this->query_build_parts['ORDER'];

        return !empty($order) ? ' ORDER BY ' . $order : '';
    }

    private function buildBaseQuery(): void
    {
        if (empty($this->query_build_parts)) {
            $this->setCustomBaseTableAlias($this->getEntity()->getDBTableName())->buildQuery();
        }
    }
}