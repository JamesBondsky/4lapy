<?php


namespace FourPaws\External;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class ImportService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @throws RuntimeException
     */
    public function setServiceLogger(): void
    {
        if (!$this->logger) {
            $this->setLogger(LoggerFactory::create('import', 'import'));
        }
    }
}
