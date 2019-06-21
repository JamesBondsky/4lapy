<?

namespace FourPaws\MobileApiBundle\Services;

class LoggerService
{

    /**
     * @var bool
     */
    private $isLogging;

    public function __construct($isLogging)
    {
        $this->setIsLogging((bool)$isLogging);
    }

    /**
     * @param bool $isLogging
     * @return $this
     */
    private function setIsLogging(bool $isLogging): self
    {
        $this->isLogging = $isLogging;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsLogging(): bool
    {
        return $this->isLogging;
    }
}