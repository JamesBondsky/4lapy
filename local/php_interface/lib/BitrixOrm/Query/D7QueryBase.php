<?php

namespace FourPaws\BitrixOrm\Query;

use Bitrix\Main\Entity\Query;
use LogicException;

abstract class D7QueryBase extends QueryBase
{
    /**
     * @var Query
     */
    private $entityQuery;

    public function __construct(Query $entityQuery)
    {
        $this->entityQuery = $entityQuery;
    }

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @return Query
     */
    public function getEntityQuery(): Query
    {
        return $this->entityQuery;
    }

    public function getNav(): array
    {
        throw new LogicException('Метод не поддерживается');
    }

    public function withNav(array $nav)
    {
        throw new LogicException('Метод не поддерживается');
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function withOffset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function withLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function doExec()
    {
        return $this->getEntityQuery()
                    ->setOrder($this->getOrder())
                    ->setFilter($this->getFilterWithBase())
                    ->setLimit($this->getLimit())
                    ->setOffset($this->getOffset())
                    ->setSelect($this->getSelectWithBase())
                    ->exec();
    }

}
