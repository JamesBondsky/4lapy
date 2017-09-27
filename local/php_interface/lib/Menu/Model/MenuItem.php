<?php

namespace FourPaws\Menu\Model;

use CIBlockElement;
use CIBlockSection;
use FourPaws\BitrixIblockORM\Model\IblockElement;

class MenuItem extends IblockElement
{
    /**
     * @var string
     */
    protected $HREF = '';

    /**
     * @var int
     */
    protected $ELEMENT_HREF = 0;

    /**
     * @var int
     */
    protected $SECTION_HREF = 0;

    /**
     * @var bool
     */
    protected $TARGET_BLANK = false;

    /**
     * @var bool
     */
    protected $BRAND_MENU = false;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        if (isset($fields['UF_TARGET_BLANK'])) {
            $this->withTargetBlank((bool)$fields['UF_TARGET_BLANK']);
        }
        if (isset($fields['PROPERTY_TARGET_BLANK_VALUE'])) {
            $this->withTargetBlank((bool)$fields['PROPERTY_TARGET_BLANK_VALUE']);
        }
        if (isset($fields['UF_BRAND_MENU'])) {
            $this->withBrandMenu((bool)$fields['UF_BRAND_MENU']);
        }

    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        if ('' != $this->HREF) {

            return $this->HREF;

        } elseif ($this->getElementHref() > 0) {

            $element = CIBlockElement::GetList(
                [],
                ['=ID' => $this->getElementHref()],
                false,
                false,
                ['IBLOCK_ID', 'ID', 'DETAIL_PAGE_URL']
            )->GetNext(false, false);
            if (false != $element && isset($element['DETAIL_PAGE_URL']) && trim($element['DETAIL_PAGE_URL']) != '') {
                $this->withHref(trim($element['DETAIL_PAGE_URL']));

                return $this->getHref();
            }

        } elseif ($this->getSectionHref() > 0) {

            $section = CIBlockSection::GetList([], ['=ID' => 1], false, ['IBLOCK_ID', 'ID', 'SECTION_PAGE_URL'])
                                     ->GetNext(false, false);
            if (false != $section && isset($section['SECTION_PAGE_URL']) && trim($section['SECTION_PAGE_URL']) != '') {
                $this->withHref(trim($section['SECTION_PAGE_URL']));

                return $this->getHref();
            }
        }

        return '';
    }

    /**
     * @param string $href
     *
     * @return $this
     */
    public function withHref(string $href)
    {
        $this->HREF = $href;

        return $this;
    }

    /**
     * @return int
     */
    public function getElementHref(): int
    {
        return (int)$this->ELEMENT_HREF;
    }

    /**
     * @param int $elementId
     *
     * @return $this
     */
    public function withElementHref(int $elementId)
    {
        $this->ELEMENT_HREF = $elementId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSectionHref(): int
    {
        return (int)$this->SECTION_HREF;
    }

    /**
     * @param int $sectionId
     *
     * @return $this
     */
    public function withSectionHref(int $sectionId)
    {
        $this->SECTION_HREF = $sectionId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTargetBlank(): bool
    {
        return $this->TARGET_BLANK;
    }

    /**
     * @param bool $targetBlank
     *
     * @return $this
     */
    public function withTargetBlank(bool $targetBlank)
    {
        $this->TARGET_BLANK = $targetBlank;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBrandMenu(): bool
    {
        return $this->BRAND_MENU;
    }

    /**
     * @param bool $brandMenu
     *
     * @return $this
     */
    public function withBrandMenu(bool $brandMenu)
    {
        $this->BRAND_MENU = $brandMenu;

        return $this;
    }

}
