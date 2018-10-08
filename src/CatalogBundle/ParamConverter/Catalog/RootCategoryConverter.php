<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Exception\LandingIsNotFoundException;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use JMS\Serializer\ArrayTransformerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RootCategoryConverter
 *
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class RootCategoryConverter extends AbstractCatalogRequestConverter
{
    use ContainerAwareTrait;

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
     * @var DataManager
     */
    private $filterSetDataManager;

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
        $this->setContainer(Application::getInstance()->getContainer());
        $this->landingService = $landingService;
        $this->filterSetDataManager = $this->container->get('bx.hlblock.filterset');

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
        return $configuration->getClass() === RootCategoryRequest::class;
    }

    /**
     * @return RootCategoryRequest
     */
    protected function getCatalogRequestObject(): RootCategoryRequest
    {
        return new RootCategoryRequest();
    }

    /**
     * @param Request             $request
     * @param ParamConverter      $configuration
     * @param RootCategoryRequest $object
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

        try {
            if ($this->landingService->isLanding($request)) {
                $object->setLanding($this->categoriesService->getDefaultLandingByDomain($this->landingService->getLandingName($request)));

                return true;
            }

            $fSet = $this->filterSetDataManager::query()
                                               ->setCacheTtl(36000)
                                               ->setFilter(
                                                   [
                                                       '=UF_URL'   => $request->getPathInfo(),
                                                       'UF_ACTIVE' => true
                                                   ]
                                               )
                                               ->setSelect(['*'])
                                               ->exec()
                                               ->fetch();

            if ($fSet && $fSet['UF_TARGET_URL']) {
                $object->setFilterSetId($fSet['ID']);
                $object->setFilterSetTarget($fSet['UF_TARGET_URL']);

                return true;
            }

            $category = $this->categoriesService->getByPath($value);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден');
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Категория %s не найдена', $value));
        } catch (LandingIsNotFoundException $e) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new NotFoundHttpException(sprintf('Лендинг %s не найдена', $this->landingService->getLandingName($request)));
        }

        try {
            $this->filterService->getFilterHelper()->initCategoryFilters($category, $request);
        } catch (\Exception $e) {
        }

        $object->setCategory($category);

        $variables = [
            'SECTION_CODE_PATH' => $request->attributes->get($pathAttribute, ''),
        ];

        $result = \CIBlockFindTools::checkSection(
            IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            $variables
        );

        if ($result) {
            $object->setCategorySlug($variables['SECTION_CODE']);
            $request->attributes->set('rootCategoryRequest', $object);
        }

        return $result;
    }
}
