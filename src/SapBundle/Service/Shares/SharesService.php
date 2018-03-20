<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Shares;

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

    /**
     * @param string $groupName
     * @param string $shareName
     *
     * @return string
     */
    public function getGroupHash(string $groupName, string $shareName): string
    {
        return md5(sprintf('%s|%s', $groupName, $shareName));
    }
}
