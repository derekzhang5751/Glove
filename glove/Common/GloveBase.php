<?php
/**
 * User: Derek
 * Date: 2018-02-28
 * Time: 12:35 PM
 */

class GloveBase extends \Bricker\RequestLifeCircle {
    protected $version = "1.0";
    protected $md5 = "";
    protected $userId = 0;
    protected $adminId = 0;

    public function __construct() {
        $userId = $GLOBALS['session']->getSessionData('user_id');
        if ( !empty($userId) ) {
            $this->userId = intval($userId);
        }
        
        $adminId = $GLOBALS['session']->getSessionData('admin_id');
        if ( !empty($adminId) ) {
            $this->adminId = intval($adminId);
        }
        
        if ($GLOBALS['LifeCfg']['ADMIN_LEVEL'] > 0) {
            if ($this->userId <= 0 && $this->adminId <= 0) {
                // redirect to login page
                //header("Location: /admin/login/do.php");
                echo "<script>window.top.location.href='/admin/login/do.php'</script>";
                exit();
            }
        }
    }
    
    protected function prepareRequestParams() {
        $this->version = isset($_POST['version']) ? trim($_POST['version']) : '1.0';
        if ($this->version == '0.5') {
            return true;
        }
        
        $this->md5 = isset($_POST['md5']) ? trim($_POST['md5']) : '';
        if ( empty($this->md5) ) {
            return false;
        }
        
        $data = isset($_POST['data']) ? trim($_POST['data']) : '';
        if ( empty($data) ) {
            return false;
        } else {
            //exit($data);
            $md5 = md5($data . MD5_KEY);
            if ($this->md5 === $md5) {
                $msg = base64_decode($data);
                $j = json_decode($msg, TRUE);
                return $j;
            } else {
                //exit($md5 . "!=" . $this->md5);
                return false;
            }
        }
        
        return true;
    }
    
    protected function process() {
        return false;
    }
    
    protected function responseHybrid() {
        exit('Not support !!');
    }
    
    protected function responseWeb() {
        exit('Not support !!');
    }
    
    protected function responseMobile() {
        exit('Not support !!');
    }
    
}
