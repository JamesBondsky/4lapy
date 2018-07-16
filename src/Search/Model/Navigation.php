<?php

namespace FourPaws\Search\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Navigation
 *
 * @package FourPaws\Search\Model
 */
class Navigation
{
    /**
     * @Serializer\SerializedName("page")
     * @Serializer\Type("int")
     * @Assert\GreaterThanOrEqual("1")
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $page = 1;

    /**
     * @Serializer\SerializedName("pageSize")
     * @Serializer\Type("int")
     * @Assert\GreaterThanOrEqual("1")
     * @Assert\Choice({"36","72"})
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $pageSize = 36;

    /**
     * @param int $page
     *
     * @return $this
     */
    public function withPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $pageSize
     *
     * @return $this
     */
    public function withPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
     */
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
