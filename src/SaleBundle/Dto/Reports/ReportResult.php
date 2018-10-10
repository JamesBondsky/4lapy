<?php
namespace FourPaws\SaleBundle\Dto\Reports;

class ReportResult
{
    /**
     * @var float
     */
    protected $progress = 0;

    /**
     * @var int
     */
    protected $countProcessed = 0;

    /**
     * @var int
     */
    protected $countTotal = 0;

    /**
     * @return float
     */
    public function getProgress(): float
    {
        return $this->progress;
    }

    /**
     * @param float $progress
     * @return ReportResult
     */
    public function setProgress(float $progress): ReportResult
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountProcessed(): int
    {
        return $this->countProcessed;
    }

    /**
     * @param int $countProcessed
     * @return ReportResult
     */
    public function setCountProcessed(int $countProcessed): ReportResult
    {
        $this->countProcessed = $countProcessed;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountTotal(): int
    {
        return $this->countTotal;
    }

    /**
     * @param int $countTotal
     * @return ReportResult
     */
    public function setCountTotal(int $countTotal): ReportResult
    {
        $this->countTotal = $countTotal;

        return $this;
    }
}
