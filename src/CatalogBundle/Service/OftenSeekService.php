<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\BitrixOrm\Utils\EntityConstructor;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
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
     * @param int $countItems
     *
     * @return ArrayCollection
     */
    public function getItemsBySection(int $sectionId, int $countItems): ArrayCollection
    {
        $result = new ArrayCollection();
        try {
            $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::RELATED_LINKS);
            $propLinkId = PropertyTable::query()->setFilter([
                'CODE'      => 'LINK',
                'IBLOCK_ID' => $iblockId,
            ])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
            $orderDirectionList = ['ASC', 'DESC'];
            $orderFieldList = ['ID', 'NAME', 'SORT'];
            /** @todo может можно по другому сделать рандомную сортирвоку в d7 */
            shuffle($orderDirectionList);
            shuffle($orderFieldList);

            $result = $this->oftenSeekRepository->findBy([
                'filter'  => [
                    '=IBLOCK_ID'         => $iblockId,
                    '=IBLOCK_SECTION_ID' => $sectionId,
                    '=ACTIVE'            => 'Y',
                    [
                        'LOGIC'       => 'OR',
                        '>=ACTIVE_TO' => new \Bitrix\Main\Type\DateTime(),
                        'ACTIVE_TO'   => null,
                    ],
                    [
                        'LOGIC'         => 'OR',
                        '<=ACTIVE_FROM' => new \Bitrix\Main\Type\DateTime(),
                        'ACTIVE_FROM'   => null,
                    ],
                ],
                'limit'   => $countItems,
                'order'   => [current($orderFieldList) => current($orderDirectionList)],
                'runtime' => [
                    new ReferenceField('PROPS', IblockPropEntityConstructor::getDataClass($iblockId),
                        Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID')),
                ],
                'select'  => ['ID', 'ACTIVE', 'NAME', 'PROPERTY_LINK' => 'PROPS.PROPERTY_' . $propLinkId],
            ]);
        } catch (\Exception $e) {
        }

        return $result;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
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
                    '=IBLOCK_ID'    => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    '<LEFT_MARGIN'  => $leftMargin,
                    '>RIGHT_MARGIN' => $rightMargin,
                    '<DEPTH_LEVEL'  => $depthLevel,
                    'ACTIVE'        => 'Y',
                ])->setSelect([
                    'ID',
                ])->setOrder(['LEFT_MARGIN' => 'desc'])->exec();

                while ($sect = $parentCatalogSections->fetch()) {
                    $catalogSections[] = (int)$sect['ID'];
                }
            }

            if (!empty($catalogSections)) {
                $catalogSections = array_unique($catalogSections);
                $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::RELATED_LINKS);
                $items = $this->oftenSeekSectionRepository->findBy([
                    'filter'  => [
                        '=IBLOCK_ID'              => IblockUtils::getIblockId(IblockType::CATALOG,
                            IblockCode::RELATED_LINKS),
                        '=USER_FIELDS.UF_SECTION' => $catalogSections,
                        '=ACTIVE'                 => 'Y',
                    ],
                    'select'  => [
                        'ID',
                        'NAME',
                        'ACTIVE',
                        'UF_SECTION' => 'USER_FIELDS.UF_SECTION',
                        'UF_COUNT'   => 'USER_FIELDS.UF_COUNT',
                    ],
                    'runtime' => [
                        new ReferenceField('USER_FIELDS',
                            EntityConstructor::compileEntityDataClass('UtsIblock' . $iblockId . 'SectionTable',
                                'b_uts_iblock_' . $iblockId . '_section')::getEntity(),
                            Join::on('this.ID', 'ref.VALUE_ID')),
                    ],
                ]);

                if (\count($catalogSections) > 1 && $items->count() > 1) {
                    /** @todo подумать над сортировкой - сейчас не изящно */
                    /** @var OftenSeekSection $item */
                    $tmpItems = $items->toArray();
                    $items->clear();
                    foreach ($catalogSections as $sectId) {
                        foreach ($tmpItems as $item) {
                            if ($sectId === $item->getCatalogSection()) {
                                $items->add($item);
                                break;
                            }
                        }
                    }
                }
                $result = $items;
            }

        } catch (\Exception $e) {

        }

        return $result;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
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
                $items = $this->getItemsBySection($section->getId(), $section->getCountItems());
                if (!$items->isEmpty()) {
                    $result = $items;
                    break;
                }
            }
        }

        return $result;
    }
}
