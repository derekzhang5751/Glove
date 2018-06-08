<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class User extends GloveBase {
    private $action;
    private $targetUserId;
    private $role;
    //private $userName;
    //private $password;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
        //$this->userName = isset($_POST['username']) ? trim($_POST['username']) : '';
        //$this->password = isset($_POST['password']) ? trim($_POST['password']) : '';
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
        } else if ($this->action == 'to_charge') {
            $this->getUserData();
        } else if ($this->action == 'to_withdraw') {
            $this->getUserData();
        } else if ($this->action == 'charge') {
            $this->doUserCharge();
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
            case 'to_charge';
                $GLOBALS['smarty']->assign("user", $this->return['data']['user']);
                $GLOBALS['smarty']->assign("operation", 'charge');
                $GLOBALS['smarty']->display('user_charge.tpl');
                break;
            case 'to_withdraw';
                $GLOBALS['smarty']->assign("user", $this->return['data']['user']);
                $GLOBALS['smarty']->assign("operation", 'withdraw');
                $GLOBALS['smarty']->display('user_charge.tpl');
                break;
            case 'charge':
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
    
    private function getUserData() {
        $this->return['data']['user'] = array();
        
        $userId = intval($this->targetUserId);
        if ($userId <= 0) {
            return false;
        }
        $user = db_get_user_by_id($userId);
        if ($user) {
            $balance = $this->getUserAvailableBalance($user);
            $this->return['data']['user'] = $user;
            $this->return['data']['user']['balance'] = $balance;
            return true;
        } else {
            return false;
        }
    }
    
    private function doUserCharge() {
        $operation = isset($_REQUEST['operation']) ? trim($_REQUEST['operation']) : 'withdraw';
        $remark = isset($_REQUEST['remark']) ? trim($_REQUEST['remark']) : '';
        
        $amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : '0';
        $amount = floatval($amount);
        if ($amount <= 0.0) {
            return false;
        }
        
        $userId = intval($this->targetUserId);
        if ($userId <= 0) {
            return false;
        }
        
        $moUser = new MoUser();
        if ($operation == 'charge') {
            return $moUser->doUserCharge($userId, $amount, OPT_SOURCE_MANUAL, $remark);
        } else {
            return $moUser->doUserWithdraw($userId, $amount, OPT_SOURCE_MANUAL, $remark);
        }
    }
    
    private function getUserBalance($user) {
        $userId = $user['user_id'];
        $balance = db_money_balance($userId);
        $balance = floatval($balance);
        $format_num = sprintf("%.2f", $balance);
        return $format_num;
    }
    
    private function getUserFrozenAmount($user) {
        $userId = $user['user_id'];
        $amount = db_order_frozen_sum($userId);
        $amount = floatval($amount);
        $format_num = sprintf("%.2f", $amount);
        return $format_num;
    }
    
    private function getUserAvailableBalance($user) {
        $balance = $this->getUserBalance($user);
        $frozen = $this->getUserFrozenAmount($user);
        $left = floatval($balance) - floatval($frozen);
        $format_num = sprintf("%.2f", $left);
        return $format_num;
    }
    
}
