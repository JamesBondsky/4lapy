<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\SectionElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockFindTools;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Exception\LandingIsNotFoundException;
use FourPaws\CatalogBundle\Exception\NoSectionsForProductException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class CategoriesService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class CategoriesService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param string $path
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws CategoryNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return Category
     */
    public function getByPath(string $path): Category
    {
        return $this->getById($this->getIdByPath($path));
    }

    /**
     * @param string $path
     *
     * @throws \FourPaws\Catalog\Exception\CategoryNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return int
     */
    public function getIdByPath(string $path): int
    {
        $path = trim($path, '/');

        if (!$path) {
            return 0;
        }

        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

        $getCategoryIDByCodePath = function () use ($path, $iblockId) {
            try {
                $categoryId = (int)CIBlockFindTools::GetSectionIDByCodePath(
                    $iblockId,
                    $path
                );
            } catch (\Exception $exception) {
                return null;
            }

            if ($categoryId <= 0) {
                //(Это сбросит запись кеша)
                return null;
            }

            return ['categoryId' => $categoryId];
        };


        try {
            $getSectionIDByCodePathResult = (new BitrixCache())
                ->withId(__METHOD__ . ':' . $path)
                ->withIblockTag($iblockId)
                ->resultOf($getCategoryIDByCodePath);
            if (isset($getSectionIDByCodePathResult['categoryId'])) {
                return (int)$getSectionIDByCodePathResult['categoryId'];
            }
        } catch (\Exception $e) {
        }

        throw new CategoryNotFoundException(
            sprintf('Категория каталога по пути `%s` не найдена.', $path)
        );
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws CategoryNotFoundException
     * @return Category
     */
    public function getById(int $id): Category
    {
        if (0 === $id) {
            return Category::createRoot();
        }

        $categoryCollection = (new CategoryQuery())->withFilterParameter('=ID', $id)
            ->exec();
        if ($categoryCollection->isEmpty()) {
            throw new CategoryNotFoundException(
                sprintf('Категория каталога #%d не найдена.', $id)
            );
        }
        if ($categoryCollection->count() > 1) {
            throw new CategoryNotFoundException(
                sprintf('Найдено более одной категории каталога с id %d', $id)
            );
        }

        return $categoryCollection->current();
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return Category
     */
    public function getSearchRoot(): Category
    {
        return Category::createRoot([
            'NAME' => 'Результаты поиска',
        ]);
    }

    /**
     * @param Product $product
     *
     * @return CategoryCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getActiveByProduct(Product $product): CategoryCollection
    {
        $sections = SectionElementTable::query()
            ->setSelect(['IBLOCK_SECTION_ID'])
            ->setFilter(['IBLOCK_ELEMENT_ID' => $product->getId()])
            ->exec();

        $sectionIds = [];
        /**
         * @var array $section
         */
        while ($section = $sections->fetch()) {
            $sectionIds[] = $section['IBLOCK_SECTION_ID'];
        }

        if (empty($sectionIds)) {
            throw new NoSectionsForProductException(\sprintf('No sections defined for product #%s', $product->getId()));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new CategoryQuery())
            ->withFilterParameter('ID', $sectionIds)
            ->withFilterParameter('ACTIVE', BitrixUtils::BX_BOOL_TRUE)
            ->withFilterParameter('SECTION_ELEMENT.IBLOCK_ELEMENT_ID', $product->getId())
            ->exec();
    }

    /**
     * @param string $landingName
     *
     * @return Category
     *
     * @throws LandingIsNotFoundException
     */
    public function getDefaultLandingByDomain(string $landingName): Category
    {
        $landing =
            $landingName
                ? (new CategoryQuery())
                ->withFilter([
                    '=UF_SUB_DOMAIN'     => $landingName,
                    'UF_DEF_FOR_LANDING' => true,
                    'ACTIVE'             => 'Y'
                ])
                ->withNav(['nTopCount' => 1])
                ->exec()
                ->first()
                : null;


        if (!$landing) {
            throw new LandingIsNotFoundException(\sprintf(
                'Landing %s is not found.',
                $landingName
            ));
        }

        return $landing;
    }

    /**
     * @param string $landingName
     *
     * @return CategoryCollection
     */
    public function getLandingCollectionByDomain(string $landingName): CategoryCollection
    {
        /**
         * @var CategoryCollection $landingCollection
         */
        $landingCollection =
            $landingName
                ? (new CategoryQuery())
                ->withFilter([
                    '=UF_SUB_DOMAIN' => $landingName,
                    'ACTIVE'         => 'Y'
                ])
                ->exec()
                : null;


        if (!$landingCollection) {
            throw new LandingIsNotFoundException(\sprintf(
                'Landing collection %s is not found.',
                $landingName
            ));
        }

        return $landingCollection;
    }
}
