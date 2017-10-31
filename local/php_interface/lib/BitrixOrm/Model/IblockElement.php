<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\BitrixOrm\Model\Traits\IblockModelTrait;

/**
 * Class IblockElement
 * @package FourPaws\BitrixOrm\Model
 *
 * TODO Добавить DATE_ACTIVE_FROM и DATE_ACTIVE_TO
 * Но где они нужны? И не будет ли тормозить, если использовать DateTimeImmutable? Или сделать его создание через lazy?
 */
abstract class IblockElement extends BitrixArrayItemBase
{
    use IblockModelTrait;
    
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
     * @var int
     */
    protected $IBLOCK_ID = 0;

    /**
     * @var string
     */
    protected $DETAIL_PAGE_URL = '';

    /**
     * @var string
     */
    protected $CANONICAL_PAGE_URL = '';

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
        if (is_null($this->previewText)) {
            $this->previewText = (new TextContent())->withText($this->PREVIEW_TEXT)
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
        if (is_null($this->detailText)) {
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

}
