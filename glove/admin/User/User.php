<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class User extends GloveBase {
    private $action;
    private $targetUserId;
    private $role;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
        $this->targetUserId = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';
        $this->role = isset($_REQUEST['role']) ? trim($_REQUEST['role']) : '';
        
        return true;
    }
    
    protected function process() {
        if ($this->action == 'list') {
            $this->getUserList();
        } else if ($this->action == 'switchrole') {
            // Switch user role
            $this->switchUserRole();
            $this->getUserList();
        }
        
        return true;
    }
    
    protected function responseWeb() {
        switch ($this->action) {
            case 'list':
            case 'switchrole';
                //exit( print_r($this->return['data']['UserList'], true) );
                //$GLOBALS['smarty']->assign("arrRoles", array(0 => '普通用户', 1 => '机器用户'));
                $GLOBALS['smarty']->assign("UserList", $this->return['data']['UserList']);
                $GLOBALS['smarty']->display('user_list.tpl');
                break;
            default:
                exit();
                break;
        }
    }
    
    private function getUserList() {
        $userList = db_get_user_list();
        if ($userList) {
            $this->return['data']['UserList'] = $userList;
        } else {
            $this->return['data']['UserList'] = array();
        }
    }
    
    private function switchUserRole() {
        $userId = intval($this->targetUserId);
        if ($userId <= 0) {
            return false;
        }
        $role = intval($this->role);
        if ($role == 0) {
            $role = 1;
        } else {
            $role = 0;
        }
        return db_set_user_role($userId, $role);
    }
    
}
