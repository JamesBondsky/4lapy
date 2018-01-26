<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class SpecialOffersRequest
{
    /**
     * Номер страницы, начиная с 1
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("page")
     * @var int
     */
    protected $page = 1;

    /**
     * Количество возвращаемых позиций
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("count")
     * @var int
     */
    protected $count = 10;

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
     * @return SpecialOffersRequest
     */
    public function setPage(int $page): SpecialOffersRequest
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return SpecialOffersRequest
     */
    public function setCount(int $count): SpecialOffersRequest
    {
        $this->count = $count;
        return $this;
    }
}
