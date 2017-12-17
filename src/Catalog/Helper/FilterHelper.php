<?php

namespace FourPaws\Catalog\Helper;

use CIBlockProperty;
use CIBlockSectionPropertyLink;
use Exception;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

class FilterHelper
{

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
     *
     * @throws Exception
     */
    public function initCategoryFilters(Category $category, Request $request)
    {
        /** @var FilterInterface $filter */
        foreach ($category->getFilters() as $filter) {
            $filter->initState($request);
        }
    }

}
