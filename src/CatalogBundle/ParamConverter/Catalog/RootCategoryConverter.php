<?php
/**
 * Created by PhpStorm.
 * User: pinchuk
 * Date: 12/24/17
 * Time: 6:43 PM
 */

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RootCategoryConverter extends AbstractCatalogRequestConverter
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
     * @param Request $request
     * @param ParamConverter $configuration
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
            $category = $this->categoriesService->getByPath($value);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден');
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Категория %s не найдена', $value));
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
