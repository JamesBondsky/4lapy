<?php

namespace FourPaws\Menu\Model;

use CIBlockElement;
use CIBlockSection;
use FourPaws\BitrixOrm\Model\IblockElement;

class MenuItem extends IblockElement
{
    /**
     * @var string
     */
    protected $PROPERTY_HREF = '';

    /**
     * @var int
     */
    protected $PROPERTY_ELEMENT_HREF = 0;

    /**
     * @var int
     */
    protected $PROPERTY_SECTION_HREF = 0;

    /**
     * @var bool
     */
    protected $PROPERTY_TARGET_BLANK = false;

    /**
     * @var bool
     */
    protected $PROPERTY_BRAND_MENU = false;

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
        if ('' != $this->PROPERTY_HREF) {

            return $this->PROPERTY_HREF;

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
        $this->PROPERTY_HREF = $href;

        return $this;
    }

    /**
     * @return int
     */
    public function getElementHref(): int
    {
        return (int)$this->PROPERTY_ELEMENT_HREF;
    }

    /**
     * @param int $elementId
     *
     * @return $this
     */
    public function withElementHref(int $elementId)
    {
        $this->PROPERTY_ELEMENT_HREF = $elementId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSectionHref(): int
    {
        return (int)$this->PROPERTY_SECTION_HREF;
    }

    /**
     * @param int $sectionId
     *
     * @return $this
     */
    public function withSectionHref(int $sectionId)
    {
        $this->PROPERTY_SECTION_HREF = $sectionId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTargetBlank(): bool
    {
        return $this->PROPERTY_TARGET_BLANK;
    }

    /**
     * @param bool $targetBlank
     *
     * @return $this
     */
    public function withTargetBlank(bool $targetBlank)
    {
        $this->PROPERTY_TARGET_BLANK = $targetBlank;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBrandMenu(): bool
    {
        return $this->PROPERTY_BRAND_MENU;
    }

    /**
     * @param bool $brandMenu
     *
     * @return $this
     */
    public function withBrandMenu(bool $brandMenu)
    {
        $this->PROPERTY_BRAND_MENU = $brandMenu;

        return $this;
    }

}
