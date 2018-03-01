<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Session;

use CSecuritySessionMC;
use CSecuritySessionVirtual;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;

class BitrixSessionBridge extends AbstractProxy implements \SessionHandlerInterface
{
    protected $class;

    public function __construct()
    {
        if (CSecuritySessionVirtual::isStorageEnabled()) {
            $this->class = 'CSecuritySessionVirtual';
        } elseif (CSecuritySessionMC::isStorageEnabled()) {
            $this->class = 'CSecuritySessionMC';
        } else {
            $this->class = 'CSecuritySessionDB';
        }
        $this->wrapper = true;
        $this->saveHandlerName = ini_get('session.save_handler');
    }


    /**
     * @inheritdoc
     */
    public function close()
    {
        return \call_user_func([$this->class, 'close']);
    }

    /**
     * @inheritdoc
     */
    public function destroy($session_id)
    {
        return \call_user_func([$this->class, 'destroy'], $session_id);
    }

    /**
     * @inheritdoc
     */
    public function gc($maxlifetime)
    {
        return \call_user_func([$this->class, 'gc'], $maxlifetime);
    }

    /**
     * @inheritdoc
     */
    public function open($save_path, $name)
    {
        return \call_user_func([$this->class, 'open'], $save_path, $name);
    }

    /**
     * @inheritdoc
     */
    public function read($session_id)
    {
        return \call_user_func([$this->class, 'read'], $session_id);
    }

    /**
     * @inheritdoc
     */
    public function write($session_id, $session_data)
    {
        return \call_user_func([$this->class, 'write'], $session_id, $session_data);
    }

    /**
     * Sets the session ID.
     *
     * @param string $id
     *
     * @throws \LogicException
     */
    public function setId($id)
    {
        if ($id === session_id()) {
            return;
        }
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the ID of an active session');
        }

        session_id($id);
    }
}
