<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\ChildCategoryFilterRequest;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ChildCategoryFilterRequestConverter
 *
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class ChildCategoryFilterRequestConverter extends AbstractCatalogRequestConverter
{
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
     *
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
        return ChildCategoryFilterRequest::class === $configuration->getClass();
    }

    /**
     * @return ChildCategoryFilterRequest
     */
    protected function getCatalogRequestObject(): ChildCategoryFilterRequest
    {
        return new ChildCategoryFilterRequest();
    }

    /**
     * @param Request                    $request
     * @param ParamConverter             $configuration
     * @param ChildCategoryFilterRequest $object
     *
     * @throws NotFoundHttpException
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object): bool
    {
        $value = (int)$request->get('section_id', 0);

        $category = (new CategoryQuery())->withFilter(['=ID' => $value])->exec()->first();
        if (\is_bool($category)) {
            throw new NotFoundHttpException(sprintf('Категория %s не найдена', $value));
        }
        try {
            $this->filterService->getFilterHelper()->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        $object->setCategory($category);
        return true;
    }
}
