<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Model\Filter\ShareFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\CatalogBundle\Dto\CatalogShareFilterRequest;
use FourPaws\CatalogBundle\Dto\CatalogShareRequest;
use FourPaws\CatalogBundle\Exception\NoBrandFilterInRootDirectory;
use FourPaws\CatalogBundle\Service\ShareService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CatalogBrandRequestConverter
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class CatalogShareFilterRequestConverter extends AbstractCatalogRequestConverter
{
    public const SHARE_PARAM = 'share_code';

    /**
     * @var ShareService
     */
    private $shareService;
    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * @param ShareService $shareService
     *
     * @required
     * @return static
     */
    public function setBrandService(ShareService $shareService)
    {
        $this->shareService = $shareService;
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
        return CatalogShareFilterRequest::class === $configuration->getClass();
    }

    /**
     * @return CatalogShareFilterRequest
     */
    protected function getCatalogRequestObject(): CatalogShareFilterRequest
    {
        return new CatalogShareFilterRequest();
    }

    /**
     * @param Request             $request
     * @param ParamConverter      $configuration
     * @param CatalogShareRequest $object
     *
     * @throws \Exception
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object): bool
    {
        $value = $request->get(static::SHARE_PARAM, '');
        if (!$value) {
            return false;
        }

        try {
            $share = $this->shareService->getByCode($value);
        } catch (\Exception $e) {
            return false;
        }

        try {
            $category = $this->shareService->getShareCategory($share);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден', $e->getCode(), $e);
        }
        try {
            $this->filterHelper->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        // $brandFilters = $category->getFilters()->filter(function (FilterInterface $filter) {
        //     return $filter instanceof ShareFilter;
        // });
        //
        // /**
        //  * @var ShareFilter $shareFilter
        //  */
        // $brandFilter = $brandFilters->current();
        //
        // if (!$brandFilter) {
        //     throw new NoBrandFilterInRootDirectory('Фильтр по бренду не найден среди фильтров корневой категории');
        // }
        // $brandFilter->setCheckedVariants([$share->getCode()]);
        // $brandFilter->setVisible(false);

        $object
            ->setShare($share)
            ->setCategory($category);
        return true;
    }
}
