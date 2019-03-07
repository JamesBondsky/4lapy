<?

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class SapConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @param $data
     *
     * @return bool
     */
    abstract public function consume($data): bool;

    /**
     * @param $data
     *
     * @return bool
     */
    abstract public function support($data): bool;

    /**
     * @return LoggerInterface
     */
    public function log(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->withLogType('sap');
            $this->logger = LoggerFactory::create($this->getLogName(), $this->getLogType());
        }

        return $this->logger;
    }
}