<?php

namespace FourPaws\Catalog\Helper;

use CIBlockProperty;
use CIBlockSectionPropertyLink;
use FourPaws\Catalog\Filter\FilterBase;
use FourPaws\Catalog\Model\Category;
use Symfony\Component\HttpFoundation\Request;

class FilterHelper
{
    /**
     * Знак разделения множественных значений фильтра
     */
    const VARIANT_DELIMITER = ',';

    /**
     * @param FilterBase $filter
     * @param Request $request
     *
     * @return string[]
     */
    public function getFilterCheckedValues(FilterBase $filter, Request $request): array
    {
        $rawValue = $request->get($filter->getCode());

        if (is_null($rawValue)) {

            return [];

        } elseif (is_string($rawValue) && strpos($rawValue, static::VARIANT_DELIMITER)) {

            return explode(static::VARIANT_DELIMITER, $rawValue);

        } elseif (is_array($rawValue)) {

            return $rawValue;

        } else {

            return [$rawValue];

        }
    }

    /**
     * Получить настройки свойств элементов в зависимости от раздела(?). В том числе позволяет понять, что включено в
     * умном фильтре.
     *
     * @param int $iblockId
     * @param int $sectionId
     *
     * @return array
     */
    public function getSectionPropertyLinks($iblockId, $sectionId = 0)
    {
        $propertyLinks = CIBlockSectionPropertyLink::GetArray($iblockId, $sectionId);
        $propIdList = array_filter(
            array_map(
                function ($propertyLinks) {
                    if (isset($propertyLinks['PROPERTY_ID']) && $propertyLinks['PROPERTY_ID'] > 0) {
                        return (int)$propertyLinks['PROPERTY_ID'];
                    } else {
                        return 0;
                    }
                },
                $propertyLinks
            ),
            function ($id) {
                return $id > 0;
            }
        );
        if (!is_array($propIdList) || !count($propIdList) > 0) {
            return $propertyLinks;
        }

        //Узнаём символьные коды свойств
        $dbPropList = CIBlockProperty::GetList([], ['=ID' => $propIdList]);
        $propCodeByIdIndex = [];
        while ($arProp = $dbPropList->Fetch()) {
            $propCodeByIdIndex[$arProp['ID']] = $arProp['CODE'];
        }

        foreach ($propertyLinks as $key => $propertyLink) {
            if (!isset($propCodeByIdIndex[$propertyLink['PROPERTY_ID']])) {
                continue;
            }

            $propertyLinks[$key]['PROPERTY_CODE'] = $propCodeByIdIndex[$propertyLink['PROPERTY_ID']];

        }

        return $propertyLinks;
    }

    /**
     * Инициализация состояния фильтров по запросу
     *
     * @param Category $category
     * @param Request $request
     */
    public function initCategoryFilters(Category $category, Request $request)
    {
        foreach ($category->getFilters() as $filter) {
            $filter->setCheckedVariants($this->getFilterCheckedValues($filter, $request));
        }
    }

}
