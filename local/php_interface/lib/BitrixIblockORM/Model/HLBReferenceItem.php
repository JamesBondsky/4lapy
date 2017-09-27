<?php

namespace FourPaws\BitrixIblockORM\Model;

abstract class HLBReferenceItem extends HLBItemBase
{
    /**
     * @var string
     */
    protected $LINK = '';

    /**
     * @var string
     */
    protected $DESCRIPTION = '';

    /**
     * @var string
     */
    protected $FULL_DESCRIPTION = '';

    //TODO UF_DEF типа "Да/Нет"
    //TODO UF_FILE типа "Файл"

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->LINK;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function withLink(string $link)
    {
        $this->LINK = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->DESCRIPTION;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function withDescription(string $description)
    {
        $this->DESCRIPTION = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullDescription(): string
    {
        return $this->FULL_DESCRIPTION;
    }

    /**
     * @param string $fullDescription
     *
     * @return $this
     */
    public function withFullDescription(string $fullDescription)
    {
        $this->FULL_DESCRIPTION = $fullDescription;

        return $this;
    }

}
