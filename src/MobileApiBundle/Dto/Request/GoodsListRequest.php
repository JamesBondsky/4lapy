<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Sort;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsListRequest
{
    /**
     * id родительской категории
     *
     * @Serializer\SerializedName("category_id")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $categoryId = 0;

    /**
     * Идентификатор города
     *
     * @Serializer\SerializedName("city_id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $cityId = '';

    /**
     * Список примененных фильтров ( ОбъектКаталога.Фильтр )
     *
     * @Serializer\SerializedName("filters")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\InputFilter>")
     * @var Filter[]
     */
    protected $filters = [];

    /**
     * Список примененных сортировок
     *
     * @Serializer\SerializedName("sorts")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\Sort>")
     * @var Sort[]
     */
    protected $sorts = [];

    /**
     * Номер страницы, начиная с 1
     *
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("int")
     * @Serializer\SerializedName("page")
     * @var int
     */
    protected $page = 1;
    /**
     * Количество товаров, начиная с 1
     *
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("int")
     * @Serializer\SerializedName("count")
     * @var int
     */
    protected $count = 10;

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     *
     * @return GoodsListRequest
     */
    public function setCategoryId(int $categoryId): GoodsListRequest
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId;
    }

    /**
     * @param string $cityId
     *
     * @return GoodsListRequest
     */
    public function setCityId(string $cityId): GoodsListRequest
    {
        $this->cityId = $cityId;
        return $this;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param Filter[] $filters
     *
     * @return GoodsListRequest
     */
    public function setFilters(array $filters): GoodsListRequest
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return Sort[]
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @param Sort[] $sorts
     *
     * @return GoodsListRequest
     */
    public function setSorts(array $sorts): GoodsListRequest
    {
        $this->sorts = $sorts;
        return $this;
    }

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
     * @return GoodsListRequest
     */
    public function setPage(int $page): GoodsListRequest
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
     * @return GoodsListRequest
     */
    public function setCount(int $count): GoodsListRequest
    {
        $this->count = $count;
        return $this;
    }
}
