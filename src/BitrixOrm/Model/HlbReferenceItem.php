<?php

namespace FourPaws\BitrixOrm\Model;

class HlbReferenceItem extends HlbItemBase
{
    /**
     * @var string
     */
    protected $UF_NAME = '';

    /**
     * @var string
     */
    protected $UF_LINK = '';
    
    /**
     * @var string
     */
    protected $UF_DESCRIPTION = '';
    
    /**
     * @var string
     */
    protected $UF_FULL_DESCRIPTION = '';

    /**
     * @var int
     */
    protected $UF_SORT = 500;

    /**
     * @var string
     */
    protected $UF_XML_ID = '';

    //TODO UF_DEF типа "Да/Нет"
    //TODO UF_FILE типа "Файл"
    
    /**
     * @return string
     */
    public function getLink() : string
    {
        return $this->UF_LINK;
    }
    
    /**
     * @param string $link
     *
     * @return $this
     */
    public function withLink(string $link)
    {
        $this->UF_LINK = $link;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->UF_DESCRIPTION;
    }
    
    /**
     * @param string $description
     *
     * @return $this
     */
    public function withDescription(string $description)
    {
        $this->UF_DESCRIPTION = $description;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFullDescription() : string
    {
        return $this->UF_FULL_DESCRIPTION;
    }
    
    /**
     * @param string $fullDescription
     *
     * @return $this
     */
    public function withFullDescription(string $fullDescription)
    {
        $this->UF_FULL_DESCRIPTION = $fullDescription;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->UF_NAME;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withName(string $name)
    {
        $this->UF_NAME = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return (int)$this->UF_SORT;
    }

    /**
     * @param int $sort
     *
     * @return $this
     */
    public function withSort(int $sort)
    {
        $this->UF_SORT = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->UF_XML_ID;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withXmlId(string $xmlId)
    {
        $this->UF_XML_ID = $xmlId;

        return $this;
    }



}
