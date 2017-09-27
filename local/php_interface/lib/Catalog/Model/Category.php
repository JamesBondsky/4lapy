<?php

namespace FourPaws\Catalog\Model;

use FourPaws\App\Application;
use FourPaws\BitrixIblockORM\Model\IblockSection;
use FourPaws\Catalog\CatalogService;
use FourPaws\Catalog\Filter\FilterBase;
use FourPaws\Catalog\Filter\FilterInterface;
use FourPaws\Catalog\Filter\FilterTrait;
use FourPaws\Catalog\Filter\Variant;
use FourPaws\Catalog\Query\CategoryQuery;
use WebArch\BitrixCache\BitrixCache;

class Category extends IblockSection implements FilterInterface
{
    use FilterTrait;

    /**
     * @var array
     */
    protected $propertyLinks;

    /**
     * @var CatalogService
     */
    protected $catalogService;

    /**
     * @var FilterBase[]
     */
    private $filterList = null;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        $this->catalogService = Application::getInstance()->getContainer()->get('catalog.service');
    }

    /**
     * @return FilterBase[]
     */
    public function getFilters(): array
    {
        //TODO Если \FourPaws\Catalog\CatalogService::getFilters будет кешироваться, можно не хранить эти данные.
        if (is_null($this->filterList)) {
            $this->filterList = $this->catalogService->getFilters($this);
        }

        return $this->filterList;
    }

    /**
     * @inheritdoc
     */
    protected function doGetAllVariants()
    {

        $doGetAllVariants = function () {
            $categoryQuery = new CategoryQuery();

            //Если это не корневой раздел
            if ($this->getId() > 0) {
                $categoryQuery->withFilterParameter('>LEFT_MARGIN', $this->getLeftMargin())
                              ->withFilterParameter('<RIGHT_MARGIN', $this->getRightMargin())
                              ->withFilterParameter('!ID', $this->getId());
            }

            $categoryCollection = $categoryQuery->withOrder(['LEFT_MARGIN' => 'ASC',])
                                                ->exec();

            $variants = [];

            /** @var Category $category */
            foreach ($categoryCollection as $category) {
                //TODO Добавить уровень вложенности, чтобы отобразить дерево, когда нужно.
                $variants[] = (new Variant())->withName($category->getName())
                                             ->withValue($category->getId());
            }

            return $variants;
        };

        /** @var Variant[] $variants */
        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getId())
                                       ->withIblockTag($this->getIblockId())
                                       ->resultOf(
                                           $doGetAllVariants
                                       );

        return $variants;
    }

    public function getFilterRule(): array
    {
        // TODO: Implement getFilterRule() method.
    }

    public function getAggRule(): array
    {
        // TODO: Implement getAggRule() method.
    }

}
