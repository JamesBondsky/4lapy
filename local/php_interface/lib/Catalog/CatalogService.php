<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use CIBlockFindTools;
use FourPaws\Catalog\Filter\FilterBase;
use FourPaws\Catalog\Helper\FilterHelper;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use JMS\Serializer\Serializer;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;

class CatalogService
{
    /**
     * @var DataManager для HL-блока Filter aka класс \FilterTable
     */
    protected $filterTable;

    /**
     * @var FilterHelper
     */
    protected $filterHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($filterTable, Serializer $serializer)
    {
        $this->filterTable = $filterTable;
        $this->serializer = $serializer;
    }

    /**
     *
     * @param Category $category
     *
     * @return FilterBase[]
     */
    public function getFilters(Category $category): array
    {
        //TODO Добавить кеширование списка фильтров

        $availableFilterList = [
            /**
             * Раздел тоже является фильтром
             */
            $category,
        ];

        $propertyLinks = $this->getFilterHelper()->getSectionPropertyLinks(
            $category->getIblockId(),
            $category->getId()
        );

        //Составить индекс по коду свойства и только для свойств, выбранных для "умного фильтра"
        $availablePropIndexByCode = [];
        foreach ($propertyLinks as $propertyLink) {
            if (
                !isset($propertyLink['SMART_FILTER'])
                || $propertyLink['SMART_FILTER'] !== BitrixUtils::BX_BOOL_TRUE
                || !isset($propertyLink['PROPERTY_CODE'])
            ) {
                continue;
            }
            $availablePropIndexByCode[$propertyLink['PROPERTY_CODE']] = true;
        }

        $dbAllFilterList = $this->filterTable::query()
                                             ->setSelect(['*'])
                                             ->setFilter(['UF_ACTIVE' => 'Y'])
                                             ->setOrder(['UF_SORT' => 'ASC'])
                                             ->exec();
        while ($filterFields = $dbAllFilterList->fetch()) {

            if (!isset($filterFields['UF_CLASS_NAME']) || !class_exists($filterFields['UF_CLASS_NAME'])) {
                LoggerFactory::create('FilterService')->warning(
                    sprintf('Filter class `%s` not found', $filterFields['UF_CLASS_NAME']),
                    $filterFields
                );
                continue;
            }
            $className = $filterFields['UF_CLASS_NAME'];

            /** @var FilterBase $curFilter */
            $curFilter = new $className($filterFields);

            //Если фильтр назначен на свойство, а его для этого раздела не выбрано, то фильтра не будет
            if ($curFilter->getPropCode() != '' && !isset($availablePropIndexByCode[$curFilter->getPropCode()])) {
                continue;
            }

            $availableFilterList[] = $curFilter;
        }

        return $availableFilterList;
    }

    /**
     * Возвращает категорию каталога с полностью настроенными фильтрами в зависимости от переданного запроса.
     *
     * @param Request $request
     *
     * @return Category|null
     * @throws RuntimeException
     */
    public function getCategory(Request $request)
    {
        $codePath = urldecode($request->getBasePath()) . '/';

        //TODO Предусмотреть создание корневой категории, когда мы в поиске или в представлении каталога по бренду

        $categoryId = $this->getCategoryIdByCodePath($codePath);
        if ($categoryId <= 0) {
            return null;
        }

        $categoryCollection = (new CategoryQuery())->withFilterParameter('=ID', $categoryId)->exec();
        if ($categoryCollection->isEmpty()) {
            throw new RuntimeException(
                sprintf('Категория каталога #%d не найдена.', $categoryId)
            );
        }
        if ($categoryCollection->count() > 1) {
            throw new RuntimeException(
                sprintf('Найдено более одной категории каталога с id %d', $categoryId)
            );
        }

        /** @var Category $category */
        $category = $categoryCollection->current();

        $this->getFilterHelper()->initCategoryFilters($category, $request);

        return $category;
    }

    /**
     * @return FilterHelper
     */
    public function getFilterHelper(): FilterHelper
    {
        if (is_null($this->filterHelper)) {
            $this->filterHelper = new FilterHelper();
        }

        return $this->filterHelper;
    }

    /**
     * @param string $codePath
     *
     * @return int
     */
    private function getCategoryIdByCodePath(string $codePath): int
    {
        $categoryId = 0;

        $getCategoryIDByCodePath = function () use ($codePath) {
            $categoryId = (int)CIBlockFindTools::GetSectionIDByCodePath(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                $codePath
            );

            if ($categoryId <= 0) {
                //(Это сбросит запись кеша)
                return null;
            }

            return ['categoryId' => $categoryId];
        };

        $getSectionIDByCodePathResult = (new BitrixCache())
            ->withId(__METHOD__ . ':' . $codePath)
            ->withIblockTag(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
            )
            ->resultOf($getCategoryIDByCodePath);

        if (isset($getSectionIDByCodePathResult['categoryId'])) {
            $categoryId = (int)$getSectionIDByCodePathResult['categoryId'];
        }

        return $categoryId;
    }

    /**
     * @param FilterBase[] $filters
     * //TODO Возможно, сюда надо добавить параметры поиска - размер страницы, сортировка, строка поиска
     *
     * @return ProductSearchResult
     */
    public function searchProducts(array $filters)
    {
        //TODO Написать метод searchProducts
        /**
         * Здесь будет отправляться запрос к Elastic, который одновременно и фильтрует, и запрашивает аггрегации
         * На основании аггрегаций нужно будет выставить доступность вариантов фильтра.
         *
         */

    }

    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

}
