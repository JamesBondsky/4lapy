<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\ShareMoreOneFoundException;
use FourPaws\Catalog\Exception\ShareNotFoundException;
use FourPaws\Catalog\Model\Filter\ShareFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\CatalogBundle\Dto\CatalogShareRequest;
use FourPaws\CatalogBundle\Service\ShareService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CatalogShareRequestConverter
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class CatalogShareRequestConverter extends AbstractCatalogRequestConverter
{
    public const SHARE_PARAM = 'share';
    
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
    public function setShareService(ShareService $shareService)
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
        return CatalogShareRequest::class === $configuration->getClass();
    }
    
    /**
     * @return CatalogShareRequest
     */
    protected function getCatalogRequestObject(): CatalogShareRequest
    {
        return new CatalogShareRequest();
    }
    
    /**
     * @param Request             $request
     * @param ParamConverter      $configuration
     * @param CatalogShareRequest $object
     *
     * @return bool
     * @throws \Exception
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object): bool
    {
        if (!$request->attributes->has(static::SHARE_PARAM)) {
            return false;
        }
        
        $value = $request->attributes->get(static::SHARE_PARAM);
        if (!$value) {
            return false;
        }
        
        try {
            $share = $this->shareService->getByCode($value);
        } catch (ShareNotFoundException $e) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        } catch (ShareMoreOneFoundException $e) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/404.php';
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
        
        $object
            ->setShare($share)
            ->setCategory($category);
        
        return true;
    }
}
