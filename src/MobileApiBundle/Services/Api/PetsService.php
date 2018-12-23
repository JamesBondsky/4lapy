<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FilterVariant;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException as MobileCategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use Psr\Log\LoggerAwareInterface;
use FourPaws\App\Application;

class PetsService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var FilterService
     */
    private $filterService;

    public function __construct(CategoriesService $categoriesService, FilterService $filterService)
    {
        $this->categoriesService = $categoriesService;
        $this->filterService = $filterService;
    }

    public function getPetsCategories()
    {
        $arResult = array();

        $breeds = $this->getPetBreeds();
        $genders = $this->getPetGenders();

        // toDo доделать когда в базе данных будет связь между категорией животного и его породой / полом
        var_dump($breeds);
        var_dump($genders);
        die();

        $oCategoryes = CIBlockSection::GetList(
            array(
                'LEFT_MARGIN' => 'ASC'
            ),
            array(
                'IBLOCK_ID' => CIBlockTools::GetIBlockId('kinds'),
                'ACTIVE' => 'Y'
            ),
            false,
            array(
                'ID',
                'NAME',
                'IBLOCK_SECTION_ID',
                'SORT',
                'UF_GENDER'
            )
        );

        while ($arCategory = $oCategoryes->Fetch())
        {
            $sid = $arCategory['ID'];
            $psid = (int)$arCategory['IBLOCK_SECTION_ID'];

            $arResult[$psid]['subcategories'][$sid] = array(
                'id' => $arCategory['ID'],
                'title' => $arCategory['NAME'],
                'gender' => array()
            );


            if (is_array($arCategory['UF_GENDER'])) {
                foreach ($arCategory['UF_GENDER'] as $sexId)
                {
                    $arResult[$psid]['subcategories'][$sid]['gender'][] = array(
                        'id' => (string)$sexId,
                        'title' => $arGenders[$sexId]
                    );
                }
            }

            if ($psid) {
                $arResult[$psid]['subcategories'][$sid]['gender'] = $arResult[$psid]['gender'];
                $arResult[$psid]['subcategories'][$sid]['breeds'] = (array)$arBreeds[$sid];
            } else {
                $arResult[$psid]['subcategories'][$sid]['sort'] = $arCategory['SORT'];
                $arResult[$psid]['gender'] = $arCategory['UF_GENDER'];
            }

            $arResult[$sid] = &$arResult[$psid]['subcategories'][$sid];
        }

        $arResult = array_shift($arResult);
        $arResult = array_shift($arResult);

        usort($arResult, $this->customSort);

        foreach ($arResult as $categoryId => $arCategory)
        {
            unset($arResult[$categoryId]['sort']);
            usort($arResult[$categoryId]['subcategories'], $this->customSort);
        }
    }

    /**
     * @return array
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getPetBreeds()
    {
        $breeds = [];
        $dataManager = Application::getHlBlockDataManager('bx.hlblock.petbreed');
        $reference = (new HlbReferenceQuery($dataManager::query()))->exec();
        /**
         * @var $item HlbReferenceItem
         */
        foreach ($reference->getValues() as $item) {
            $breeds[$item->getXmlId()] = [
                'id' => $item->getXmlId(),
                'title' => $item->getName()
            ];
        }
        return $breeds;
    }

    public function getPetGenders()
    {
        $genders = [];
        $dataManager = Application::getHlBlockDataManager('bx.hlblock.petgender');
        $reference = (new HlbReferenceQuery($dataManager::query()))->exec();
        /**
         * @var $item HlbReferenceItem
         */
        foreach ($reference->getValues() as $item) {
            $genders[$item->getXmlId()] = $item->getName();
        }
        return $genders;
    }

    private function customSort($a, $b)
    {
        if (isset($a['sort']) && $a['sort'] != $b['sort']) {
            return $a['sort'] > $b['sort'] ? 1 : -1;
        }
        if ($a['title'] == 'Другое') {
            return 1;
        } elseif ($b['title'] == 'Другое') {
            return -1;
        }
        return strcmp($a['title'], $b['title']);
    }


}
