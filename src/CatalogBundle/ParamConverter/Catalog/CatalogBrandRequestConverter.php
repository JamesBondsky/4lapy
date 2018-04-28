<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\BrandMoreOneFoundException;
use FourPaws\Catalog\Exception\BrandNotFoundException;
use FourPaws\Catalog\Model\Filter\BrandFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use FourPaws\CatalogBundle\Exception\NoBrandFilterInRootDirectory;
use FourPaws\CatalogBundle\Service\BrandService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CatalogBrandRequestConverter
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class CatalogBrandRequestConverter extends AbstractCatalogRequestConverter
{
    public const BRAND_PARAM = 'brand';

    /**
     * @var BrandService
     */
    private $brandService;
    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * @param BrandService $brandService
     *
     * @required
     * @return static
     */
    public function setBrandService(BrandService $brandService)
    {
        $this->brandService = $brandService;
        return $this;
    }

    /**
     * @param FilterHelper $filterHelper
     *
     * @required
     * @return static
     */
    public function setFilterHelper(FilterHelper $filterHelper)
    {
        $this->filterHelper = $filterHelper;
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
        return CatalogBrandRequest::class === $configuration->getClass();
    }

    /**
     * @return CatalogBrandRequest
     */
    protected function getCatalogRequestObject() : CatalogBrandRequest
    {
        return new CatalogBrandRequest();
    }

    /**
     * @param Request             $request
     * @param ParamConverter      $configuration
     * @param CatalogBrandRequest $object
     *
     * @throws \Exception
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object) : bool
    {
        if (!$request->attributes->has(static::BRAND_PARAM)) {
            return false;
        }

        $value = $request->attributes->get(static::BRAND_PARAM);
        if (!$value) {
            return false;
        }

        try {
            $brand = $this->brandService->getByCode($value);
        } catch (BrandNotFoundException $e) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/404.php';
        } catch (BrandMoreOneFoundException $e) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/404.php';
        }

        try {
            $category = $this->brandService->getBrandCategory($brand);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден', $e->getCode(), $e);
        }
        try {
            $this->filterHelper->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        $brandFilters = $category->getFilters()->filter(function (FilterInterface $filter) {
            return $filter instanceof BrandFilter;
        });

        /**
         * @var BrandFilter $brandFilter
         */
        $brandFilter = $brandFilters->current();

        if (!$brandFilter) {
            throw new NoBrandFilterInRootDirectory('Фильтр по бренду не найден среди фильтров корневой категории');
        }
        $brandFilter->setCheckedVariants([$brand->getCode()]);
        $brandFilter->setVisible(false);

        $object
            ->setBrand($brand)
            ->setCategory($category);
        return true;
    }
}
