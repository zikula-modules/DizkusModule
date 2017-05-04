<?php

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
    private $coreUser = null;

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
        $this->active = isset($data['active']) ? $data['active'] : false;
        $this->server = isset($data['server']) ? $data['server'] : '';
        $this->port = isset($data['port']) ? $data['port'] : 110;
        $this->login = isset($data['login']) ? $data['login'] : '';
        $this->password = isset($data['password']) ? $data['password'] : '';
        $this->interval = isset($data['interval']) ? $data['interval'] : 0;
        $this->lastconnect = isset($data['lastconnect']) ? $data['lastconnect'] : 0;
        $this->coreUser = isset($data['coreUser']) ? $data['coreUser'] : null;
        $this->matchstring = isset($data['matchstring']) ? $data['matchstring'] : '';
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
