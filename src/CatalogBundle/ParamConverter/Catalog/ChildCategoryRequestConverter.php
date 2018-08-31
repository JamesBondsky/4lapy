<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\CatalogBundle\Service\SortService;
use JMS\Serializer\ArrayTransformerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ChildCategoryRequestConverter
 *
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class ChildCategoryRequestConverter extends AbstractCatalogRequestConverter
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
     * @var CatalogLandingService
     */
    private $landingService;

    /**
     * AbstractCatalogRequestConverter constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface        $validator
     * @param SortService               $sortService
     * @param CatalogLandingService     $landingService
     */
    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator,
        SortService $sortService,
        CatalogLandingService $landingService
    )
    {
        $this->landingService = $landingService;

        parent::__construct($arrayTransformer, $validator, $sortService);
    }

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
        return ChildCategoryRequest::class === $configuration->getClass();
    }

    /**
     * @return ChildCategoryRequest
     */
    protected function getCatalogRequestObject(): ChildCategoryRequest
    {
        return new ChildCategoryRequest();
    }

    /**
     * @param Request              $request
     * @param ParamConverter       $configuration
     * @param ChildCategoryRequest $object
     *
     * @throws NotFoundHttpException
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object): bool
    {
        $options = $configuration->getOptions();
        $pathAttribute = $options['path'] ?? 'path';

        if (!$request->attributes->has($pathAttribute)) {
            return false;
        }

        $value = $request->attributes->get($pathAttribute, '');

        if ($this->landingService->isLanding($request)) {
            $object->setIsLanding(true);
            $object->setLandingDomain($this->landingService->getLandingDomain($request));
            $object->setLandingDocRoot($this->landingService->getLandingDocRoot($request));
            $object->setLandingCollection($this->categoriesService->getLandingCollectionByDomain($this->landingService->getLandingName($request)));
        }

        try {
            $category = $this->categoriesService->getByPath($value);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден');
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Категория %s не найдена', $value));
        }

        try {
            $this->filterService
                ->getFilterHelper()
                ->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        $object->setCategory($category);
        $object->setCurrentPath($request->getPathInfo());

        return true;
    }
}
