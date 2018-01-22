<?php

namespace FourPaws\BitrixOrm\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;
use CIBlockElement;
use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\Traits\IblockModelTrait;
use FourPaws\BitrixOrm\Type\TextContent;

/**
 * Class IblockSect
 * @package FourPaws\BitrixOrm\Model
 *
 */
class IblockSect extends BitrixArrayItemBase
{

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
    protected $CODE = '';
    
    /**
     * @var int
     */
    protected $IBLOCK_SECTION_ID = '';
    
    /**
     * @var int
     */
    protected $PICTURE = '';
    
    /**
     * @var int
     */
    protected $DETAIL_PICTURE = '';
    
    /**
     * @var int
     */
    protected $DEPTH_LEVEL = '';
    
    /**
     * @var string
     */
    protected $DESCRIPTION = '';

    /**
     * @var string
     */
    protected $DESCRIPTION_TYPE = '';

    /**
     * @var TextContent
     */
    protected $description;

    /**
     * @var string
     */
    protected $SECTION_PAGE_URL = '';
    

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
    }

    /**
     * @return string
     */
    public function getSectionPageUrl()
    {
        return $this->SECTION_PAGE_URL;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function withSectionPageUrl(string $url)
    {
        $this->SECTION_PAGE_URL = $url;

        return $this;
    }

    /**
     * @return TextContent
     */
    public function geDescription(): TextContent
    {
        if (null === $this->description) {
            $this->description = (new TextContent())
                ->withText($this->DESCRIPTION)
                ->withType($this->DESCRIPTION_TYPE);
        }

        return $this->description;
    }

    /**
     * @param TextContent $description
     *
     * @return $this
     */
    public function withDescription(TextContent $description)
    {
        $this->description = $description;

        return $this;
    }
    
    /**
     * @return int
     */
    public function getIblockId() : int
    {
        return $this->IBLOCK_ID;
    }
    
    /**
     * @param int $iblockId
     *
     * @return $this
     */
    public function withIblockId(int $iblockId)
    {
        $this->IBLOCK_ID = $iblockId;
    
        return $this;
    }
    
    /**
     * @return int
     */
    public function getCode() : int
    {
        return $this->CODE;
    }
    
    /**
     * @param int $code
     *
     * @return $this
     */
    public function withCode(int $code)
    {
        $this->CODE = $code;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getIblockSectionId() : int
    {
        return $this->IBLOCK_SECTION_ID;
    }
    
    /**
     * @param int $iblockSectionId
     *
     * @return $this
     */
    public function withIblockSectionId(int $iblockSectionId)
    {
        $this->IBLOCK_SECTION_ID = $iblockSectionId;
        return $this;
        
    }
    
    /**
     * @return int
     */
    public function getPicture() : int
    {
        return $this->PICTURE;
    }
    
    /**
     * @param int $picture
     *
     * @return $this
     */
    public function withPicture(int $picture)
    {
        $this->PICTURE = $picture;
        return $this;
        
    }
    
    /**
     * @return int
     */
    public function getDetailPicture() : int
    {
        return $this->DETAIL_PICTURE;
    }
    
    /**
     * @param int $detailPicture
     *
     * @return $this
     */
    public function withDetailPicture(int $detailPicture)
    {
        $this->DETAIL_PICTURE = $detailPicture;
    
        return $this;
    }
    
    /**
     * @return int
     */
    public function getDepthLevel() : int
    {
        return $this->DEPTH_LEVEL;
    }
    
    /**
     * @param int $depthLevel
     *
     * @return $this
     */
    public function withDepthLevel(int $depthLevel)
    {
        $this->DEPTH_LEVEL = $depthLevel;
    
        return $this;
    }
}
