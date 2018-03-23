<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuy;
use FourPaws\SapBundle\Service\Shares\SharesService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class BonusBuyConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class BonusBuyConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    /**
     * @var SharesService
     */
    private $sharesService;

    /**
     * BonusBuyConsumer constructor.
     *
     * @param SharesService $sharesService
     */
    public function __construct(SharesService $sharesService)
    {
        $this->sharesService = $sharesService;
    }

    /**
     * Consume bonus buy promo actions
     *
     * @param $data
     *
     * @throws RuntimeException
     * @return bool
     */
    public function consume($data): bool
    {
        if (!$this->support($data)) {
            return false;
        }

        $this->log()->log(LogLevel::INFO, 'Импортируется акция из Bonus Buy');
        
        try {
            $success = true;

            $this->sharesService->export($data);
            die;
        } catch (\Exception $e) {
            $success = false;

            $this->log()->log(LogLevel::ERROR, \sprintf('Импортируется акции: %s', $e->getMessage()));
        }
        
        return $success;
    }
    
    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof BonusBuy;
    }
}
