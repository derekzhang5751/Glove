<?php
/**
 * User: Derek
 * Date: 2018-06-11
 */

class SyncTime extends GloveBase {
    private $action = '';
    private $achatName = '';
    private $groupName = '';
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    public function __construct() {
        //
    }
    
    protected function prepareRequestParams() {
        $postData = parent::prepareRequestParams();
        if ($postData) {
            if ($this->version == '1.0') {
                $_POST['action']     = $postData['action'];
                $_POST['achat_name'] = $postData['achat_name'];
                $_POST['group_name'] = $postData['group_name'];
            }
        } else {
            return false;
        }
        
        $this->action = isset($_POST['action']) ? trim($_POST['action']) : '';
        if ( empty($this->action) ) {
            return false;
        }
        
        $this->achatName = isset($_POST['achat_name']) ? trim($_POST['achat_name']) : '';
        if ( empty($this->achatName) ) {
            return false;
        }
        
        $this->groupName = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';
        if ( empty($this->groupName) ) {
            return false;
        }
        
        return true;
    }
    
    protected function process() {
        $data = array();
        if ($this->action == 'GET_UTC') {
            $now = microtime(true);
            $this->return['data']['utc'] = $now;
        }
        
        $this->return['success'] = true;
        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
}
