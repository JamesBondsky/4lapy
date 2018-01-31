<?php

namespace FourPaws\ProductAutoSort;

use Adv\Bitrixtools\Exception\IblockPropertyNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\SectionTable;
use CIBlockElement;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\ProductAutoSort\Helper\PropertyHelper;
use FourPaws\ProductAutoSort\Helper\ValueHelper;
use FourPaws\ProductAutoSort\Table\ElementPropertyConditionTable;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ProductAutoSortService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $valueHelper;

    /**
     * @var PropertyHelper
     */
    protected $propertyHelper;

    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('ProductAutoSortService'));
    }

    /**
     * Определяет, к каким категориям подходят товары.
     *
     * @param int[] $productIdList
     *
     * @return array формата ID товара => [ID категории 1, ID категории 2, ... , ID категории N]
     */
    public function defineProductsCategories(array $productIdList)
    {
        $productIdList = array_filter(
            $productIdList,
            function ($id) {
                return $id > 0;
            }
        );

        if (empty($productIdList)) {
            throw new InvalidArgumentException('Пустой список ID товаров');
        }

        /**
         * Раунд 1: извлечь товары с их торговыми предложениями и по их свойствам отфильтровать категории-кандидаты,
         * к которым они могут подходить.
         */
        $candidateCategoryList = $this->getCandidateCategories($productIdList);

        /**
         * Раунд 2: для каждой категории-кандидата сформировать запрос на фильтрацию товаров и торговых предложений
         * на основании условий свойств для категории + ограничить результат по списку ID товаров и получить какие
         * товары и торговые предложения будут находиться в категории.
         */
        $productsToCategoryIndex = $this->testCandidateCategories($candidateCategoryList, $productIdList);

        /**
         * Раунд 3: сформировать результаты предыдущего раунда в формате для выхода.
         */
        return $this->convertToCategoriesToProducts($productsToCategoryIndex);
    }

    /**
     * Определяет, к каким категориям подходят товары для всего каталога.
     *
     * @return array формата ID товара => [ID категории 1, ID категории 2, ... , ID категории N]
     */
    public function defineAllProductsCategories()
    {
        $allCategoryList = [];

        $dbCategoryCandidatesList = ElementPropertyConditionTable::query()
            ->setSelect(['*'])
            ->exec();
        /**
         * Потенциально тонкое место: проверяя категорию, запрашивать разом все товары каталога.
         * Может потребоваться порционное ограничение, если не будет хватать вычислительных ресурсов.
         */
        while ($candidate = $dbCategoryCandidatesList->fetch()) {
            $allCategoryList[$candidate['SECTION_ID']][] = $candidate;
        }

        $productsToCategoryIndex = $this->testCandidateCategories($allCategoryList);

        return $this->convertToCategoriesToProducts($productsToCategoryIndex);
    }

    /**
     * Синхронизирует значение для условия свойства элемента
     *
     * @param int   $ufId
     * @param int   $sectionId
     * @param int   $propertyId
     * @param mixed $value
     */
    public function syncValue(int $ufId, int $sectionId, int $propertyId, $value)
    {
        $this->getValueHelper()->syncValue($ufId, $sectionId, $propertyId, $value);
    }

    /**
     * Синхронизирует множество значений для условия свойства элемента
     *
     * @param int   $ufId
     * @param int   $sectionId
     * @param array $valueList
     */
    public function syncValueMulti(int $ufId, int $sectionId, array $valueList)
    {
        $this->getValueHelper()->syncValueMulti($ufId, $sectionId, $valueList);
    }

    /**
     * Удалить все значения для категории.
     *
     * @param int $sectionId
     */
    public function deleteValue(int $sectionId)
    {
        $this->getValueHelper()->deleteValue($sectionId);
    }

    /**
     * @return ValueHelper
     */
    public function getValueHelper()
    {
        if (null === $this->valueHelper) {
            $this->valueHelper = new ValueHelper();
        }

        return $this->valueHelper;
    }

    /**
     * @return PropertyHelper
     */
    public function getPropertyHelper()
    {
        if (null === $this->propertyHelper) {
            $this->propertyHelper = new PropertyHelper();
        }

        return $this->propertyHelper;
    }

    /**
     * @return LoggerInterface
     */
    public function log()
    {
        return $this->logger;
    }

    /**
     * @param int[] $productIdList
     *
     * @return array Структуры: ID категории => [ условие1, условие2, ..., условиеM ]
     */
    private function getCandidateCategories(array $productIdList)
    {
        $candidateCategoryList = [];

        $productCollection = (new ProductQuery())->withFilter(['=ID' => $productIdList])->exec();

        $categoryFilter = [];

        /** @var Product $product */
        foreach ($productCollection as $product) {
            $productAsArray = $product->toArray();

            $categoryFilter = $this->appendToRawCandidateFilter(
                $categoryFilter,
                $productAsArray['PROPERTY_VALUES'],
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
            );

            /** @var Offer $offer */
            foreach ($product->getOffers() as $offer) {
                $offerAsArray = $offer->toArray();

                $categoryFilter = $this->appendToRawCandidateFilter(
                    $categoryFilter,
                    $offerAsArray['PROPERTY_VALUES'],
                    IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)
                );
            }
        }

        if (empty($categoryFilter)) {
            return [];
        }

        $categoryFilter['LOGIC'] = 'OR';
        $categoryFilter = [
            'UF_ID' => $this->getPropertyHelper()->getUfPropCondIdForProducts(),
            $categoryFilter,
        ];

        //Сначала узнаём ID категорий кандидатов
        $dbCategoryCandidatesList = ElementPropertyConditionTable::query()
            ->setFilter($categoryFilter)
            ->setSelect(['SECTION_ID'])
            ->setGroup(['SECTION_ID'])
            ->exec();

        $candidateIdList = [];

        while ($row = $dbCategoryCandidatesList->fetch()) {
            $candidateIdList[] = (int)$row['SECTION_ID'];
        }

        if (empty($candidateIdList)) {
            return [];
        }

        //Для кандидатов извлекаем все их условия
        $dbCategoryCandidatesList = ElementPropertyConditionTable::query()
            ->setFilter(['=SECTION_ID' => $candidateIdList])
            ->setSelect(['*'])
            ->exec();
        while ($candidate = $dbCategoryCandidatesList->fetch()) {
            $candidateCategoryList[$candidate['SECTION_ID']][] = $candidate;
        }

        return $candidateCategoryList;
    }

    /**
     * @param array $categoryFilter
     * @param array $elementPropertyValues
     * @param int   $iblockId
     *
     * @return array
     */
    private function appendToRawCandidateFilter($categoryFilter, array $elementPropertyValues, int $iblockId)
    {
        foreach ($elementPropertyValues as $code => $value) {
            try {
                $propertyId = IblockUtils::getPropertyId($iblockId, $code);

                //Если множественное пустое свойство, то это эквивалентно незаполненности свойства
                if (
                    \is_array($value) &&
                    \count($value) == 0 &&
                    $this->getPropertyHelper()->isMultipleProperty($propertyId)
                ) {
                    $value = null;
                }

                if (!$this->getPropertyHelper()->isMultipleProperty($propertyId)) {
                    $categoryFilter = $this->appendToRawCandidateFilterNewValue(
                        $categoryFilter,
                        $propertyId,
                        $value
                    );
                } else {
                    //Множественное свойство раскладываем на отдельные значения
                    foreach ($value as $singleValue) {
                        $categoryFilter = $this->appendToRawCandidateFilterNewValue(
                            $categoryFilter,
                            $propertyId,
                            $singleValue
                        );
                    }
                }
            } catch (IblockPropertyNotFoundException $exception) {
                /**
                 * Является ошибкой преобразования PROPERTY_BRAND.NAME в код свойства, а потому не важно.
                 */
            }
        }

        return $categoryFilter;
    }

    /**
     * @param array $candidateCategoryList
     * @param int[] $productIdList
     *
     * @return array Структуры: ID категории => [ID товара1, ID товара2, ..., ID товараN]
     */
    private function testCandidateCategories(array $candidateCategoryList, array $productIdList = [])
    {
        $productsToCategoryIndex = [];

        foreach ($candidateCategoryList as $categoryId => $conditionList) {

            /**
             * Проверка по товарам (с ограничением по списку ID товаров)
             */
            $productConditions = array_filter(
                $conditionList,
                function ($condition) {
                    return
                        \is_array($condition)
                        && isset($condition['PROPERTY_ID'])
                        && $this->getPropertyHelper()->isProductProperty($condition['PROPERTY_ID']);
                }
            );

            $matchingProducts = $this->getMatchingProducts($productConditions, $productIdList);

            /**
             * Проверка по торговым предложениям (с ограничением по списку ID товаров)
             */
            $offerConditions = array_filter(
                $conditionList,
                function ($condition) {
                    return
                        \is_array($condition)
                        && isset($condition['PROPERTY_ID'])
                        && $this->getPropertyHelper()->isOfferProperty($condition['PROPERTY_ID']);
                }
            );

            //Если нет условий для проверки торговых предложений
            if (empty($offerConditions)) {
                //То мы уже нашли подходящие товары
                $productsToCategoryIndex[$categoryId] = $matchingProducts;
                continue;
            }

            $matchingOffers = $this->getMatchingOffers($offerConditions, $productIdList);

            //Продукты, которые и по своим условиям, и по условиям для торговых предложений подходят под критерии.
            $productsToCategoryIndex[$categoryId] = array_intersect($matchingProducts, $matchingOffers);
        }

        return $productsToCategoryIndex;
    }

    /**
     * @param array $productConditionList
     * @param array $productIdList
     *
     * @return array
     */
    private function getMatchingProducts(array $productConditionList, array $productIdList)
    {
        $matchingProducts = [];

        $productFilter = $this->makeRawElementFilterByProperty($productConditionList);

        if (!empty($productFilter)) {
            $productFilter['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
            if (!empty($productIdList)) {
                $productFilter['=ID'] = $productIdList;
            }

            $dbMatchingProducts = CIBlockElement::GetList(
                [],
                $productFilter,
                false,
                false,
                ['IBLOCK_ID', 'ID']
            );
            while ($product = $dbMatchingProducts->Fetch()) {
                $matchingProducts[] = (int)$product['ID'];
            }
        }

        return $matchingProducts;
    }

    /**
     * @param $offerConditions
     * @param $productIdList
     *
     * @return array
     */
    private function getMatchingOffers($offerConditions, $productIdList)
    {
        $matchingProducts = [];

        $offerFilter = $this->makeRawElementFilterByProperty($offerConditions);

        if (!empty($offerFilter)) {
            $offerFilter['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
            if (!empty($productIdList)) {
                $offerFilter['=PROPERTY_CML2_LINK'] = $productIdList;
            }

            $dbMatchingOffers = CIBlockElement::GetList(
                [],
                $offerFilter,
                false,
                false,
                ['IBLOCK_ID', 'ID', 'PROPERTY_CML2_LINK']
            );
            while ($offer = $dbMatchingOffers->Fetch()) {
                $matchingProducts[] = (int)$offer['PROPERTY_CML2_LINK_VALUE'];
            }
        }

        return $matchingProducts;
    }

    /**
     * Формирует промежуточный (сырой) фильтр по свойствам на основе условий
     *
     * @param array $conditionList
     *
     * @return array
     */
    private function makeRawElementFilterByProperty(array $conditionList): array
    {
        $rawPropFilter = [];

        foreach ($conditionList as $condition) {

            //Производится преобразование null в false, что означает "свойство не заполнено".
            $value = $condition['PROPERTY_VALUE'] ?? false;

            //Чтобы при повторении условия по этому свойству срабатывало условие "ИЛИ"
            $rawPropFilter[$condition['PROPERTY_ID']]['LOGIC'] = 'OR';

            $rawPropFilter[$condition['PROPERTY_ID']][] = ['PROPERTY_' . $condition['PROPERTY_ID'] => $value];
        }

        return $rawPropFilter;
    }

    /**
     * @param array $productsToCategoryIndex
     *
     * @return array
     */
    private function convertToCategoriesToProducts(array $productsToCategoryIndex)
    {
        $categoriesToProductIndex = [];

        if (empty($productsToCategoryIndex)) {
            return $categoriesToProductIndex;
        }

        $categoryDepthLevels = [];
        $categories = SectionTable::getList(
            [
                'filter' => [
                    'ID' => array_keys($categoriesToProductIndex),
                ],
                'select' => ['ID', 'DEPTH_LEVEL'],
            ]
        );
        while ($category = $categories->fetch()) {
            $categoryDepthLevels[$category['ID']] = $category['DEPTH_LEVEL'];
        }

        uksort(
            $productsToCategoryIndex,
            function ($categoryId1, $categoryId2) use ($categoryDepthLevels) {
                return $categoryDepthLevels[$categoryId1] > $categoryDepthLevels[$categoryId2] ? -1 : 1;
            }
        );

        foreach ($productsToCategoryIndex as $categoryId => $productIdList) {
            foreach ($productIdList as $productId) {
                $categoriesToProductIndex[$productId][] = $categoryId;
            }
        }

        return $categoriesToProductIndex;
    }

    /**
     * @param array $categoryFilter
     * @param int   $propertyId
     * @param       $value
     *
     * @return array
     */
    private function appendToRawCandidateFilterNewValue(array $categoryFilter, int $propertyId, $value)
    {
        //Т.к. нужна фильтрация по конкретному значению, нельзя пропускать значения в виде массива.
        if (\is_array($value)) {
            return $categoryFilter;
        }

        /**
         * Исключение создания дублирующихся условий
         */
        if (isset($categoryFilter[$propertyId])) {
            foreach ($categoryFilter[$propertyId] as $condition) {
                if (
                    \is_array($condition)
                    && array_key_exists('PROPERTY_ID', $condition)
                    && array_key_exists('PROPERTY_VALUE', $condition)
                    && $condition['PROPERTY_ID'] == $propertyId
                    && $condition['PROPERTY_VALUE'] == $value

                ) {
                    return $categoryFilter;
                }
            }
        }

        $categoryFilter[$propertyId]['LOGIC'] = 'OR';
        $categoryFilter[$propertyId][] = [
            'PROPERTY_ID'    => $propertyId,
            'PROPERTY_VALUE' => $value,
        ];

        return $categoryFilter;
    }
}
