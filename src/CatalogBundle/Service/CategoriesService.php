<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use CIBlockFindTools;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WebArch\BitrixCache\BitrixCache;

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

        $categoryCollection = (new CategoryQuery())->withFilterParameter('=ID', $id)->exec();
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
}
