<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

function compare_user_data($a, $b) {
    if ($a['won'] == $b['won']) {
        return 0;
    } else if ($a['won'] > $b['won']) {
        return -1;
    } else {
        return 1;
    }
}

class main extends GloveBase {
    private $action;
    private $userName;
    private $dayBegin;
    private $dayEnd;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
        //$this->userName = isset($_POST['username']) ? trim($_POST['username']) : '';
        $this->dayBegin = isset($_REQUEST['daybegin']) ? trim($_REQUEST['daybegin']) : '';
        $this->dayEnd = isset($_REQUEST['dayend']) ? trim($_REQUEST['dayend']) : '';
        
        return true;
    }
    
    protected function process() {
        $this->userName = $GLOBALS['session']->getSessionData('admin_name');
        if ($this->action == 'main') {
            //
        } else if ($this->action == 'monitor') {
            $this->doMonitor();
        }
        return true;
    }
    
    protected function responseWeb() {
        $GLOBALS['smarty']->assign("UserName", $this->userName);
        switch ($this->action) {
            case 'main':
                $GLOBALS['smarty']->display('right.tpl');
                break;
            case 'monitor':
                $GLOBALS['smarty']->assign("dayBegin", $this->dayBegin);
                $GLOBALS['smarty']->assign("dayEnd", $this->dayEnd);
                $GLOBALS['smarty']->assign("UserList", $this->return['data']['UserList']);
                $GLOBALS['smarty']->display('monitor.tpl');
                break;
            case 'head':
                $GLOBALS['smarty']->display('head.tpl');
                break;
            case 'menu':
                $GLOBALS['smarty']->display('left.tpl');
                break;
            default:
                $GLOBALS['smarty']->display('main.tpl');
                break;
        }
    }
    
    private function doMonitor() {
        if ($this->dayEnd == '') {
            $today = date("Y-m-d");
            $this->dayEnd = date("Y-m-d", strtotime($today . " +1 day"));
        }
        if ($this->dayBegin == '') {
            $this->dayBegin = date("Y-m-d", strtotime($this->dayEnd . " -1 day"));
        }
        //
        $this->return['data']['UserList'] = array();
        $userList = db_get_user_list();
        if ($userList) {
            foreach ($userList as $user) {
                $won = db_user_won_amount_by_period($user['user_id'], $this->dayBegin, $this->dayEnd);
                $allOrdersCost = db_get_user_orders_cost_by_period($user['user_id'], $this->dayBegin, $this->dayEnd);
                $balance = $this->getUserAvailableBalance($user);
                $userName = $user['user_name'];
                if ($user['role'] == 1) {
                    $userName = $userName . ' [机器人]';
                }
                $item = array(
                    'user_id' => $user['user_id'],
                    'user_name' => $userName,
                    'role' => $user['role'],
                    'total' => sprintf("%.2f", $allOrdersCost),
                    'won' => sprintf("%.2f", $won),
                    'balance' => $balance,
                );
                array_push($this->return['data']['UserList'], $item);
            }
            usort($this->return['data']['UserList'], 'compare_user_data');
        }
        // sum
        $size = count($this->return['data']['UserList']);
        if ($size > 0) {
            $sumCoat = 0;
            $sumWon = 0.0;
            $sumBalance = 0.0;
            foreach ($this->return['data']['UserList'] as $user) {
                $sumCoat = $sumCoat + floatval($user['total']);
                $sumWon = $sumWon + floatval($user['won']);
                $sumBalance = $sumBalance + floatval($user['balance']);
            }
            $item = array(
                'user_id' => 0,
                'user_name' => '合计',
                'role' => 99,
                'total' => sprintf("%.2f", $sumCoat),
                'won' => sprintf("%.2f", $sumWon),
                'balance' => sprintf("%.2f", $sumBalance),
            );
            array_push($this->return['data']['UserList'], $item);
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
