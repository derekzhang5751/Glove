<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class User extends GloveBase {
    private $action;
    private $userName;
    private $password;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_GET['act']) ? trim($_GET['act']) : '';
        $this->userName = isset($_POST['username']) ? trim($_POST['username']) : '';
        $this->password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        return true;
    }
    
    protected function process() {
        if ($this->action == 'list') {
            $userList = db_get_user_list();
            if ($userList) {
                $this->return['data']['UserList'] = $userList;
            } else {
                $this->return['data']['UserList'] = array();
            }
        }
        return true;
    }
    
    protected function responseWeb() {
        switch ($this->action) {
            case 'list':
                //exit( print_r($this->return['data']['UserList'], true) );
                $GLOBALS['smarty']->assign("UserList", $this->return['data']['UserList']);
                $GLOBALS['smarty']->display('user_list.tpl');
                break;
            default:
                exit();
                break;
        }
    }
    
}
