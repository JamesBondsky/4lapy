<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchRequestConverter extends AbstractCatalogRequestConverter
{
    const SORT_DEFAULT = 'relevance';

    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @param CategoriesService $categoriesService
     *
     * @required
     * @return static
     */
    public function setCategoriesService(CategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
        return $this;
    }

    /**
     * @param FilterService $filterService
     * @return static
     * @required
     */
    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        return SearchRequest::class === $configuration->getClass();
    }

    /**
     * @return SearchRequest
     */
    protected function getCatalogRequestObject(): SearchRequest
    {
        return new SearchRequest();
    }

    /**
     * @param Request              $request
     * @param ParamConverter       $configuration
     * @param SearchRequest $object
     *
     * @throws NotFoundHttpException
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object): bool
    {
        try {
            $category = $this->categoriesService->getByPath('');
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден');
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Корневая категория не найдена'));
        }
        try {
            $this->filterService->getFilterHelper()->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        $object->setCategory($category);
        return true;
    }
}
