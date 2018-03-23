<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Shares;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuy;
use FourPaws\SapBundle\Repository\ShareRepository;
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
    /**
     * @var ShareRepository
     */
    private $repository;

    /**
     * SharesService constructor.
     *
     * @param ShareRepository $repository
     * @param Serializer $serializer
     */
    public function __construct(ShareRepository $repository, Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->repository = $repository;
    }

    /**
     * @param string $groupName
     * @param string $shareName
     *
     * @return string
     */
    public function getGroupHash(string $groupName, string $shareName): string
    {
        return \md5(\sprintf('%s|%s', $groupName, $shareName));
    }

    /**
     * @param BonusBuy $dto
     */
    public function export(BonusBuy $dto): void
    {
        /**
         * @todo
         *
         * create
         * update
         * delete
         */
    }
}
