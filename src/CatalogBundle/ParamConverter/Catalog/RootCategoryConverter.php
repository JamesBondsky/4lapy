<?php
/**
 * Created by PhpStorm.
 * User: pinchuk
 * Date: 12/24/17
 * Time: 6:43 PM
 */

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Service\CategoriesService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RootCategoryConverter implements ParamConverterInterface
{
    /**
     * @var CategoriesService
     */
    private $categoriesService;

    public function __construct(CategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }


    /**
     * Stores the object in the request.
     *
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();

        if (!$request->attributes->has($name)) {
            return false;
        }

        $value = $request->attributes->get($name, '');
        try {
            $category = $this->categoriesService->getByPath($value);
        } catch (IblockNotFoundException $e) {
            throw new NotFoundHttpException('Инфоблок каталога не найден');
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Категория %s не найдена', $value));
        }
        $rootCategoryRequest = (new RootCategoryRequest())
            ->setCategory($category);
        $request->attributes->set($name, $rootCategoryRequest);
        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === RootCategoryRequest::class;
    }
}
