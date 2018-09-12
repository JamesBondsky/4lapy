<?php

namespace FourPaws\BitrixOrm\Model;

class HlbReferenceItem extends HlbItemBase
{
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

    //TODO UF_DEF типа "Да/Нет"
    /**
     * @var int
     */
    protected $UF_FILE = 0;

    /**
     * @var string
     */
    protected $UF_CODE = '';

    /**
     * @var int
     */
    protected $UF_BASE_VALUE = '';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->UF_CODE;
    }

    /**
     * @param string $code
     *
     * @return static
     */
    public function withCode(string $code)
    {
        $this->UF_CODE = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
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
    public function getDescription(): string
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
    public function getFullDescription(): string
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
     * @return int
     */
    public function getFile(): int
    {
        return $this->UF_FILE;
    }

    /**
     * @param int $file
     *
     * @return $this
     */
    public function withFile(int $file)
    {
        $this->UF_FILE = $file;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseValue(): int
    {
        return (int)$this->UF_BASE_VALUE;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function withBaseValue(int $id)
    {
        $this->UF_BASE_VALUE = $id;
        return $this;
    }
}
