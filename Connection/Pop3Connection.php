<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Connection;

/**
 * Class to define a connection to a pop3 server
 */
class Pop3Connection
{
    /**
     * active
     *
     * boolean
     */
    private $active = false;

    /**
     * server
     *
     * string
     */
    private $server = '';

    /**
     * port
     *
     * integer
     */
    private $port = 110;

    /**
     * login
     *
     * string
     */
    private $login = '';

    /**
     * password
     *
     * string
     */
    private $password = '';

    /**
     * interval
     *
     * integer
     */
    private $interval = 0;

    /**
     * lastconnect
     *
     * integer
     */
    private $lastconnect = 0;

    /**
     * Zikula\UsersModule\Entity\UserEntity
     *
     * object
     */
    private $coreUser;

    /**
     * matchstring
     *
     * string
     */
    private $matchstring = '';

    /**
     * Constructor.
     * @param array $data connection details
     */
    public function __construct($data)
    {
        $this->active = $data['active'] ?? false;
        $this->server = $data['server'] ?? '';
        $this->port = $data['port'] ?? 110;
        $this->login = $data['login'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->interval = $data['interval'] ?? 0;
        $this->lastconnect = $data['lastconnect'] ?? 0;
        $this->coreUser = $data['coreUser'] ?? null;
        $this->matchstring = $data['matchstring'] ?? '';
    }

    /**
     * Get connection details
     * @return array connection details
     */
    public function getConnection()
    {
        return [
            'server' => $this->server,
            'port' => $this->port,
            'login' => $this->login,
            'password' => $this->password,
            'interval' => $this->interval,
            'lastconnect' => $this->lastconnect,
            'coreUser' => $this->coreUser,
            'matchstring' => $this->matchstring];
    }

    /**
     * get connection status
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function updateConnectTime()
    {
        $this->lastconnect = time();
    }
}
