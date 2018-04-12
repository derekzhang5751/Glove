<?php
/**
 * Created by PhpStorm.
 * User: Derek
 * Date: 2018-01-12
 * Time: 11:16 AM
 */

namespace Bricker;


interface ISession
{
    public function init($db, $sessionId = '', $deviceType = DEVICE_WEB, $autoCreate = false);
    public function getSessionId();
    public function setSessionData($name, $value);
    public function getSessionData($name);
    public function deleteSession();
}