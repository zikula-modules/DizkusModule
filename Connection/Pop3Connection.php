<?php

/**
 * Copyright Dizkus Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Dizkus
 * @link https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Dizkus\Connection;

/**
 * Class to define a connection to a pop3 server
 *
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
     * Zikula\Module\UsersModule\Entity\UserEntity
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
        return array(
            'server' => $this->server,
            'port' => $this->port,
            'login' => $this->login,
            'password' => $this->password,
            'interval' => $this->interval,
            'lastconnect' => $this->lastconnect,
            'coreUser' => $this->coreUser,
            'matchstring' => $this->matchstring);
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
