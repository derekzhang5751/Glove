<?php
/**
 * User: Derek
 * Date: 2018-04-08
 */

class GloveSession implements \Bricker\ISession
{
    private $db             = NULL;
    private $deviceType     = DEVICE_WEB;
    private $sessionTable   = 'sessions';
    private $sessionKey     = '';
    private $autoCreate     = false;
    private $sessionData    = array();
    private $sessionName    = 'GLOVE_ID';
    private $_ip            = '';
    private $_time;
    
    private $max_life_time  = 600; // unit: second
    private $session_cookie_path   = '/';
    private $session_cookie_domain = '';
    private $session_cookie_secure = false;
    
    public function init($db, $sessionId = '', $deviceType = DEVICE_WEB, $autoCreate = false) {
        $this->db = $db;
        $this->deviceType = $deviceType;
        $this->sessionKey = $sessionId;
        $this->autoCreate = $autoCreate;
        
        if ( empty($this->sessionKey) ) {
            $this->sessionKey = isset($_COOKIE[$this->sessionName]) ? $_COOKIE[$this->sessionName] : '';
        }
        
        if ( !empty($GLOBALS['cookie_path']) ) {
            $this->session_cookie_path = $GLOBALS['cookie_path'];
        } else {
            $this->session_cookie_path = '/';
        }
        
        if ( !empty($GLOBALS['cookie_domain']) ) {
            $this->session_cookie_domain = $GLOBALS['cookie_domain'];
        } else {
            $this->session_cookie_domain = '';
        }
        
        if ( !empty($GLOBALS['cookie_secure']) ) {
            $this->session_cookie_secure = $GLOBALS['cookie_secure'];
        } else {
            $this->session_cookie_secure = false;
        }
        
        $this->_ip   = \Bricker\client_real_ip();
        $this->_time = time();
        
        if ($this->sessionKey) {
            $this->load_session();
        } else {
            if ($this->autoCreate) {
                $this->gen_session_id();
                $this->load_session();
                setcookie($this->sessionName, $this->sessionKey, 0, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
            }
        }
        
        register_shutdown_function(array($this, 'close_session'));
    }
    
    private function gen_session_id() {
        $sessionId = md5(uniqid(mt_rand(), true));
        $this->sessionKey = substr($sessionId, 0, 32);
        $this->sessionData[$this->sessionName] = $this->sessionKey;
        
        return $this->db_insert_session();
    }
    
    private function load_session() {
        $session = $this->db_get_session();
        if (empty($session)) {
            $this->db_insert_session();
        } else {
            if ( !empty($session['data']) ) {
                $this->sessionData = unserialize($session['data']);
            }
        }
    }
    
    public function close_session() {
        $this->db_save_session();
        $this->db_delete_session_expired();
    }
    
    public function deleteSession() {
        $this->sessionData = array();
        
        setcookie($this->sessionName, '', 1, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
        return $this->db_delete_session();
    }
    
    public function getSessionId() {
        return $this->sessionKey;
    }
    
    public function setSessionData($name, $value) {
        $this->sessionData[$name] = $value;
    }
    
    public function getSessionData($name) {
        if (isset($this->sessionData[$name])) {
            return $this->sessionData[$name];
        } else {
            return '';
        }
    }
    
    public function getClientIp() {
        return $this->_ip;
    }
    
    private function db_insert_session() {
        $data = array(
            'session_key' => $this->sessionKey,
            'expiry'      => $this->_time + $this->max_life_time,
            'user_id'     => 0,
            'admin_id'    => 0,
            'ip'          => $this->_ip,
            'data'        => serialize($this->sessionData)
        );
        $stat = $GLOBALS['db']->insert($this->sessionTable, $data);
        if ($stat->rowCount() == 1) {
            return $GLOBALS['db']->id();
        } else {
            return false;
        }
    }
    
    private function db_get_session() {
        $session = $GLOBALS['db']->get($this->sessionTable,
            ['id', 'session_key', 'expiry', 'user_id', 'admin_id', 'ip', 'data'],
            [
                'session_key' => $this->sessionKey
            ]
        );
        if ($session) {
            return $session;
        } else {
            return false;
        }
    }
    
    private function db_save_session() {
        $userId = isset($this->sessionData['user_id']) ? $this->sessionData['user_id'] : 0;
        $adminId = isset($this->sessionData['admin_id']) ? $this->sessionData['admin_id'] : 0;
        
        $state = $GLOBALS['db']->update($this->sessionTable,
            [
                'expiry'   => $this->_time + $this->max_life_time,
                'user_id'  => $userId,
                'admin_id' => $adminId,
                'ip'       => $this->_ip,
                'data'     => serialize($this->sessionData)
            ],
            [
                'session_key' => $this->sessionKey
            ]
        );
        
        if ($state->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function db_delete_session() {
        $state = $GLOBALS['db']->delete($this->sessionTable,
            [
                'session_key' => $this->sessionKey
            ]
        );
        
        if ($state->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function db_delete_session_expired() {
        $state = $GLOBALS['db']->delete($this->sessionTable,
            [
                'expiry[<=]'    => time(),
                'user_id[<=]'  => 0,
                'admin_id[<=]' => 0
            ]
        );
        
        if ($state->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
