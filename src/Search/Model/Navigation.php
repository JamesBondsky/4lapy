<?php

namespace FourPaws\Search\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Navigation
{
    /**
     * @Serializer\SerializedName("page")
     * @Serializer\Type("int")
     * @Assert\GreaterThanOrEqual("1")
     * @Assert\NotBlank()
     * @var int
     */
    protected $page = 1;

    /**
     * @Serializer\SerializedName("pageSize")
     * @Serializer\Type("int")
     * @Assert\GreaterThanOrEqual("1")
     * @Assert\Choice({"20","40"})
     * @Assert\NotBlank()
     * @var int
     */
    protected $pageSize = 20;

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
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->getPageSize();
    }
}
