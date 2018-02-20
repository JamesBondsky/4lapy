<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Sale\SectionTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\OftenSeekSection;
use FourPaws\CatalogBundle\Repository\OftenSeekRepository;
use FourPaws\CatalogBundle\Repository\OftenSeekSectionRepository;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class OftenSeekService implements OftenSeekInterface
{

    private $oftenSeekRepository;
    private $oftenSeekSectionRepository;

    public function __construct(
        OftenSeekRepository $oftenSeekRepository,
        OftenSeekSectionRepository $oftenSeekSectionRepository
    ) {
        $this->oftenSeekRepository = $oftenSeekRepository;
        $this->oftenSeekSectionRepository = $oftenSeekSectionRepository;
    }

    /**
     * @param int $sectionId
     *
     * @return ArrayCollection
     */
    public function getItemsBySection(int $sectionId): ArrayCollection
    {
        $result = new ArrayCollection();
        try {
            $this->oftenSeekRepository->findBy([
                'filter' => [
                    '=IBLOCK_ID'         => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::RELATED_LINKS),
                    '=IBLOCK_SECTION_ID' => $sectionId,
                    '=ACTIVE'            => 'Y',
                ],
                'select' => [],
            ]);
        } catch (\Exception $e) {
        }
        return $result;
    }

    /**
     * @param int $sectionId
     *
     * @param int $leftMargin
     * @param int $rightMargin
     *
     * @param int $depthLevel
     *
     * @return ArrayCollection
     */
    public function getSectionsByCatalogSection(
        int $sectionId,
        int $leftMargin = 0,
        int $rightMargin = 0,
        int $depthLevel = 0
    ): ArrayCollection {
        $result = new ArrayCollection();
        try {
            //Получаем структуру разделов каталога
            $catalogSections = [$sectionId];
            if ($leftMargin <= 0 || $rightMargin <= 0 || $depthLevel <= 0) {
                $catalogSect = SectionTable::query()->setFilter([
                    '=IBLOCK_ID'  => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    '=UF_SECTION' => $sectionId,
                ])->setSelect([
                    'ID',
                    'DEPTH_LEVEL',
                    'LEFT_MARGIN',
                    'RIGHT_MARGIN',
                ])->exec()->fetch();
                $leftMargin = $catalogSect['LEFT_MARGIN'];
                $rightMargin = $catalogSect['RIGHT_MARGIN'];
                $depthLevel = $catalogSect['DEPTH_LEVEL'];
            }
            if ($depthLevel > 1) {
                $parentCatalogSections = SectionTable::query()->setFilter([
                    '=IBLOCK_ID'     => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    '>=LEFT_MARGIN'  => $leftMargin,
                    '<=RIGHT_MARGIN' => $rightMargin,
                    'ACTIVE'         => 'Y',
                ])->setSelect([
                    'ID',
                ])->setOrder(['LEFT_MARGIN' => 'asc'])->exec();
                while ($sect = $parentCatalogSections->fetch()) {
                    $catalogSections[] = $sect['ID'];
                }
            }

            if (!empty($catalogSections)) {
                $items = $this->oftenSeekSectionRepository->findBy([
                    'filter' => [
                        '=IBLOCK_ID'  => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::RELATED_LINKS),
                        '=UF_SECTION' => $catalogSections,
                        '=ACTIVE'     => 'Y',
                    ],
                    'select' => [
                        'ID',
                        'NAME',
                        'ACTIVE',
                        'UF_SECTION',
                        'UF_COUNT',
                    ],
                ]);

                if (\count($catalogSections) > 1) {
                    /** @todo подумать над сортировкой - сейчас не изящно */
                    /** @var OftenSeekSection $item */
                    $tmpItems = $items;
                    $items = [];
                    foreach ($catalogSections as $sectId) {
                        foreach ($tmpItems as $item) {
                            if ($sectId === $item->getCatalogSection()) {
                                $result[] = $item;
                                break;
                            }
                        }
                    }
                }
                $result = new ArrayCollection($items);
            }

        } catch (\Exception $e) {
        }
        return $result;
    }

    /**
     * @param int $sectionId
     *
     * @param int $leftMargin
     * @param int $rightMargin
     *
     * @param int $depthLevel
     *
     * @return ArrayCollection
     */
    public function getItems(
        int $sectionId,
        int $leftMargin = 0,
        int $rightMargin = 0,
        int $depthLevel = 0
    ): ArrayCollection {
        $result = new ArrayCollection();
        $sections = $this->getSectionsByCatalogSection($sectionId, $leftMargin, $rightMargin, $depthLevel);
        if (!$sections->isEmpty()) {
            /** @var OftenSeekSection $section */
            foreach ($sections as $section) {
                $items = $this->getItemsBySection($section->getId());
                if (!$items->isEmpty()) {
                    $result = $items;
                    break;
                }
            }
        }
        return $result;
    }
}
