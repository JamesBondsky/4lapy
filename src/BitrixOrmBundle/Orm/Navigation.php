<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

class Navigation
{
    /**
     * @var null|int
     */
    protected $page;

    /**
     * @var null|int
     */
    protected $pageSize;

    /**
     * @var null|int
     */
    protected $elementId;

    /**
     * @var null|bool
     */
    protected $showAll;

    /**
     * @var null|mixed
     */
    protected $topCount;

    /**
     * @return null|int
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param null|int $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return null|int
     */
    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    /**
     * @param null|int $pageSize
     */
    public function setPageSize(?int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @return null|int
     */
    public function getElementId(): ?int
    {
        return $this->elementId;
    }

    /**
     * @param null|int $elementId
     */
    public function setElementId(?int $elementId): void
    {
        $this->elementId = $elementId;
    }

    /**
     * @return null|bool
     */
    public function getShowAll(): ?bool
    {
        return $this->showAll;
    }

    /**
     * @param null|bool $showAll
     */
    public function setShowAll(?bool $showAll): void
    {
        $this->showAll = $showAll;
    }

    /**
     * @return null|mixed
     */
    public function getTopCount(): ?mixed
    {
        return $this->topCount;
    }

    /**
     * @param null|mixed $topCount
     */
    public function setTopCount(?mixed $topCount): void
    {
        $this->topCount = $topCount;
    }

    public function toArray(): array
    {
        $result = [
            'nTopCount'  => $this->getTopCount(),
            'bShowAll'   => $this->getShowAll(),
            'iNumPage'   => $this->getPage(),
            'nPageSize'  => $this->getPageSize(),
            'nElementID' => $this->getElementId(),
        ];

        return array_filter($result, function ($value) {
            return null !== $value;
        });
    }
}
