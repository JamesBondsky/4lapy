<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\CatalogBundle\Dto\AbstractCatalogRequest;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\Search\Model\Navigation;
use JMS\Serializer\ArrayTransformerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractCatalogRequestConverter implements ParamConverterInterface
{
    const SORT_TYPE = 'sort';
    const SEARCH_STRING = 'q';

    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var SortService
     */
    private $sortService;

    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator,
        SortService $sortService
    ) {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
        $this->sortService = $sortService;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $object = $this->getCatalogRequestObject();
        $result =
            $this->configureBase($request, $configuration, $object) &&
            $this->configureCustom($request, $configuration, $object);

        if ($result) {
            $request->attributes->set($configuration->getName(), $object);
        }
        return $result;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    abstract public function supports(ParamConverter $configuration);

    /**
     * @param Request $request
     *
     * @return Navigation
     */
    protected function getNavigation(Request $request): Navigation
    {
        $navigation = $this->arrayTransformer->fromArray($request->query->all(), Navigation::class);
        /**
         * @var Navigation $navigation
         */
        $navigation = $navigation instanceof Navigation ? $navigation : new Navigation();

        return $this->validator->validate($navigation)->count() ? new Navigation() : $navigation;
    }

    /**
     * @param Request $request
     *
     * @return SortsCollection
     */
    protected function getSortsCollection(Request $request)
    {
        return $this->sortService->getSorts(
            $request->query->get(static::SORT_TYPE, ''),
            (bool)$this->getSearchString($request)
        );
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getSearchString(Request $request)
    {
        return $request->query->get(static::SEARCH_STRING, '');
    }

    /**
     * @param Request                $request
     * @param AbstractCatalogRequest $catalogRequest
     *
     * @return bool
     */
    protected function configureBase(
        Request $request,
        ParamConverter $configuration,
        AbstractCatalogRequest $catalogRequest
    ) {
        $catalogRequest
            ->setNavigation($this->getNavigation($request))
            ->setSearchString($this->getSearchString($request))
            ->setSorts($this->getSortsCollection($request));
        return true;
    }

    /**
     * @return AbstractCatalogRequest
     */
    abstract protected function getCatalogRequestObject();

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @param                $object
     *
     * @return bool
     */
    abstract protected function configureCustom(Request $request, ParamConverter $configuration, $object);
}
