<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashMessageService
{
    public const FLASH_TYPE_NOTICE = 'notice';
    public const FLASH_TYPE_ERROR  = 'error';

    /**
     * @var FlashBagInterface
     */
    protected $flashBag;

    /**
     * FlashMessageService constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->flashBag = $session->getFlashBag();
    }

    /**
     * @param string $message
     * @param string $type
     */
    public function add(string $message, string $type = self::FLASH_TYPE_NOTICE): void
    {
        $this->flashBag->add($type, $message);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function get(string $type = self::FLASH_TYPE_NOTICE): array
    {
        return $this->flashBag->get($type);
    }
}