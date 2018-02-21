<?php

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerAwareInterface;

/**
 * Class SharesService
 *
 * @package FourPaws\SapBundle\Service\Shares
 */
class SharesService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createOrUpdate()
    {
        /**
         * @todo
         */
    }

    public function delete()
    {

    }
}
