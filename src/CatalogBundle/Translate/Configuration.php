<?php

namespace FourPaws\CatalogBundle\Translate;

/**
 * Class Configuration
 *
 * DTO конфигурации битриксового экспорта.
 *
 * @package FourPaws\CatalogBundle\Translate
 */
class Configuration implements Conf
{
    protected $iblockId           = 0;
    protected $siteId             = '';
    protected $serverName         = '';
    protected $companyName        = '';
    protected $exportFile         = '';
    protected $sectionIds         = [];
    protected $xmlData            = [];
    protected $isHttps            = false;
    protected $isFilterAvailable  = false;
    protected $isReferrersDisable = false;
    protected $maxExecutionTime   = 0;
    protected $isPermissionsCheck = false;

    /**
     * @return int
     */
    public function getIblockId(): int
    {
        return $this->iblockId;
    }

    /**
     * @param int $iblockId
     *
     * @return Configuration
     */
    public function setIblockId(int $iblockId): Configuration
    {
        $this->iblockId = $iblockId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId;
    }

    /**
     * @param string $siteId
     *
     * @return Configuration
     */
    public function setSiteId(string $siteId): Configuration
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     *
     * @return Configuration
     */
    public function setServerName(string $serverName): Configuration
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     *
     * @return Configuration
     */
    public function setCompanyName(string $companyName): Configuration
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getExportFile(): string
    {
        return $this->exportFile;
    }

    /**
     * @param string $exportFile
     *
     * @return Configuration
     */
    public function setExportFile(string $exportFile): Configuration
    {
        $this->exportFile = $exportFile;

        return $this;
    }

    /**
     * @return array
     */
    public function getXmlData(): array
    {
        return $this->xmlData;
    }

    /**
     * @param array $xmlData
     *
     * @return Configuration
     */
    public function setXmlData(array $xmlData): Configuration
    {
        $this->xmlData = $xmlData;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHttps(): bool
    {
        return $this->isHttps;
    }

    /**
     * @param bool $isHttps
     *
     * @return Configuration
     */
    public function setIsHttps(bool $isHttps): Configuration
    {
        $this->isHttps = $isHttps;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsFilterAvailable(): bool
    {
        return $this->isFilterAvailable;
    }

    /**
     * @param bool $isFilterAvailable
     *
     * @return Configuration
     */
    public function setIsFilterAvailable(bool $isFilterAvailable): Configuration
    {
        $this->isFilterAvailable = $isFilterAvailable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsReferrersDisable(): bool
    {
        return $this->isReferrersDisable;
    }

    /**
     * @param bool $isReferrersDisable
     *
     * @return Configuration
     */
    public function setIsReferrersDisable(bool $isReferrersDisable): Configuration
    {
        $this->isReferrersDisable = $isReferrersDisable;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxExecutionTime(): int
    {
        return $this->maxExecutionTime;
    }

    /**
     * @param int $maxExecutionTime
     *
     * @return Configuration
     */
    public function setMaxExecutionTime(int $maxExecutionTime): Configuration
    {
        $this->maxExecutionTime = $maxExecutionTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsPermissionsCheck(): bool
    {
        return $this->isPermissionsCheck;
    }

    /**
     * @param bool $isPermissionsCheck
     *
     * @return Configuration
     */
    public function setIsPermissionsCheck(bool $isPermissionsCheck): Configuration
    {
        $this->isPermissionsCheck = $isPermissionsCheck;

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionIds(): array
    {
        return $this->sectionIds;
    }

    /**
     * @param array $sectionIds
     *
     * @return $this
     */
    public function setSectionIds(array $sectionIds): self
    {
        $this->sectionIds = $sectionIds;

        return $this;
    }
}
