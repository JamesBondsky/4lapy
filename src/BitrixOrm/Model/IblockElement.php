<?php

namespace FourPaws\BitrixOrm\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;
use CIBlockElement;
use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\Traits\IblockModelTrait;
use FourPaws\BitrixOrm\Type\TextContent;

/**
 * Class IblockElement
 * @package FourPaws\BitrixOrm\Model
 *
 */
abstract class IblockElement extends BitrixArrayItemBase
{
    use IblockModelTrait;

    /**
     * @var int
     * @JMS\Serializer\Annotation\Type("int")
     * @see BitrixArrayItemBase
     */
    protected $IBLOCK_ID = 0;

    /**
     * @var int
     * @JMS\Serializer\Annotation\Type("int")
     * @see BitrixArrayItemBase
     */
    protected $ID = 0;

    /**
     * @var int
     * @JMS\Serializer\Annotation\Type("int")
     * @see BitrixArrayItemBase
     */
    protected $SORT = 500;

    /**
     * @var string
     */
    protected $PREVIEW_TEXT = '';

    /**
     * @var string
     */
    protected $PREVIEW_TEXT_TYPE = '';

    /**
     * @var TextContent
     */
    protected $previewText;

    /**
     * @var string
     */
    protected $DETAIL_TEXT = '';

    /**
     * @var string
     */
    protected $DETAIL_TEXT_TYPE = '';

    /**
     * @var TextContent
     */
    protected $detailText;

    /**
     * @var string
     */
    protected $DETAIL_PAGE_URL = '';

    /**
     * @var string
     */
    protected $CANONICAL_PAGE_URL = '';

    /**
     * @var string
     */
    protected $DATE_ACTIVE_FROM = '';

    /**
     * @var string
     */
    protected $DATE_ACTIVE_TO = '';

    /**
     * @var DateTimeImmutable
     */
    protected $dateActiveFrom;

    /**
     * @var DateTimeImmutable
     */
    protected $dateActiveTo;

    /**
     * @var int[] ID всех разделов инфоблока, к которым прикреплён элемент.
     * @JMS\Serializer\Annotation\Type("int")
     * @JMS\Serializer\Annotation\Accessor(getter="getSectionsIdList")
     * @see BitrixArrayItemBase
     */
    protected $sectionIdList;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
    }

    /**
     * @return string
     */
    public function getDetailPageUrl()
    {
        return $this->DETAIL_PAGE_URL;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function withDetailPageUrl(string $url)
    {
        $this->DETAIL_PAGE_URL = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getCanonicalPageUrl(): string
    {
        return $this->CANONICAL_PAGE_URL;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function withCanonicalPageUrl(string $url)
    {
        $this->CANONICAL_PAGE_URL = $url;

        return $this;
    }

    /**
     * @return TextContent
     */
    public function getPreviewText(): TextContent
    {
        if (null === $this->previewText) {
            $this->previewText = (new TextContent())
                ->withText($this->PREVIEW_TEXT)
                ->withType($this->PREVIEW_TEXT_TYPE);
        }

        return $this->previewText;
    }

    /**
     * @param TextContent $previewText
     *
     * @return $this
     */
    public function withPreviewText(TextContent $previewText)
    {
        $this->previewText = $previewText;

        return $this;
    }

    /**
     * @return TextContent
     */
    public function getDetailText(): TextContent
    {
        if (null === $this->detailText) {
            $this->detailText = (new TextContent())->withText($this->DETAIL_TEXT)
                ->withType($this->DETAIL_TEXT_TYPE);
        }

        return $this->detailText;
    }

    /**
     * @param TextContent $detailText
     *
     * @return $this
     */
    public function withDetailText(TextContent $detailText)
    {
        $this->detailText = $detailText;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getDateActiveFrom()
    {
        if (null === $this->dateActiveFrom && $this->DATE_ACTIVE_FROM) {
            $this->dateActiveFrom = BitrixUtils::bitrixStringDateTime2DateTimeImmutable($this->DATE_ACTIVE_FROM);
        }

        return $this->dateActiveFrom;
    }

    /**
     * @param DateTimeImmutable $dateActiveFrom
     *
     * @return $this
     */
    public function withDateActiveFrom(DateTimeImmutable $dateActiveFrom)
    {
        $this->dateActiveFrom = $dateActiveFrom;
        $this->DATE_ACTIVE_FROM = BitrixUtils::dateTimeImmutable2BitrixStringDate($dateActiveFrom, 'FULL');

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getDateActiveTo()
    {
        if (null === $this->dateActiveTo && $this->DATE_ACTIVE_TO) {
            $this->dateActiveTo = BitrixUtils::bitrixStringDateTime2DateTimeImmutable($this->DATE_ACTIVE_TO);
        }

        return $this->dateActiveTo;
    }

    /**
     * @param DateTimeImmutable $dateActiveTo
     *
     * @return $this
     */
    public function withDateActiveTo(DateTimeImmutable $dateActiveTo)
    {
        $this->dateActiveTo = $dateActiveTo;
        $this->DATE_ACTIVE_TO = BitrixUtils::dateTimeImmutable2BitrixStringDate($dateActiveTo, 'FULL');

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionsIdList(): array
    {
        if (
            null === $this->sectionIdList
            || (\is_array($this->sectionIdList) && \count($this->sectionIdList) === 0)
        ) {
            $this->sectionIdList = [];
            $dbSectionList = CIBlockElement::GetElementGroups($this->getId(), true, ['ID']);

            while ($section = $dbSectionList->Fetch()) {
                $this->sectionIdList[] = (int)$section['ID'];
            }
        }

        return $this->sectionIdList;
    }
}
