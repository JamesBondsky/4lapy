<?php

namespace FourPaws\Search\Model;

class Navigation
{
    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $pageSize = 25;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function withPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     *
     * @return $this
     */
    public function withPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getFrom(): int
    {
        return ($this->getPage() - 1) * $this->getPageSize();
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->getPageSize();
    }

}
