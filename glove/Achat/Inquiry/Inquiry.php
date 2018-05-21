<?php
/**
 * User: Derek
 * Date: 2018-05-07
 */

class Inquiry extends GloveBase {
    private $action = '';
    private $achatName = '';
    private $groupName = '';
    private $issueNum = '';
    
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
                $_POST['issue_num']  = isset($postData['issue_num']) ? $postData['issue_num'] : '';
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
        
        $this->issueNum = trim($_POST['issue_num']);
        
        return true;
    }
    
    protected function process() {
        $data = array();
        if ($this->action == 'verify') {
            $data = $this->doVerify();
        } else if ($this->action == 'result') {
            $data = $this->doRelease();
        }
        
        $this->return['success'] = true;
        $this->return['data'] = $data;
        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    private function doVerify() {
        $ret = array(
            'issue_num' => '',
            'user_list' => array()
        );
        $type = $this->getCurrentLotteryType();
        $nextIssue = db_get_next_issue($type);
        if ($nextIssue) {
            $issueNum = $nextIssue['issue_num'];
            $ret['issue_num'] = $issueNum;
        } else {
            return $ret;
        }
        
        // get all members in the group
        $users = db_get_users_by_group($this->achatName, $this->groupName);
        
        // get all orders of member
        foreach ($users as $user) {
            $us = array(
                'nick_name' => $user['nick_name'],
                'balance' => $this->getUserBalance($user),
                'orders' => array()
            );
            
            $orders = db_get_order_for_verify($user['user_id'], $issueNum);
            $od = array();
            foreach ($orders as $order) {
                $strOrder = $this->stringOrder($order);
                array_push($od, $strOrder);
            }
            
            $us['orders'] = $od;
            array_push($ret['user_list'], $us);
        }
        
        return $ret;
    }
    
    private function doRelease() {
        $ret = array(
            'issue_num' => '',
            'user_list' => array()
        );
        $issueNum = 0;
        if (empty($this->issueNum)) {
            return $ret;
        } else {
            $issueNum = intval($this->issueNum) - 1;
            $ret['issue_num'] = strval($issueNum);
        }
        
        // get all members in the group
        $users = db_get_users_by_group($this->achatName, $this->groupName);
        
        // get all won orders of member
        foreach ($users as $user) {
            $orders = db_get_order_for_won($user['user_id'], $issueNum);
            $od = array();
            foreach ($orders as $order) {
                $strOrder = $this->stringOrder($order);
                $str = '('.$user['nick_name'].')'.$strOrder.'='.$order['amount'];
                array_push($ret['user_list'], $str);
            }
        }
        
        return $ret;
    }
    
    private function getCurrentLotteryType() {
        $curTime = intval( date("Hi") );
        if ($curTime > 3 && $curTime < 430) {
            return LOTTERY_XYFT;
        } else {
            return LOTTERY_PK10;
        }
    }
    
    private function stringOrder($order) {
        $strOrder = '';
        $line = intval($order['line']);
        if ($line == 99) {
            $strOrder = '冠亚和';
        } else {
            switch ($line) {
                case 1:
                    $strOrder = '冠  军';
                    break;
                case 2:
                    $strOrder = '亚  军';
                    break;
                case 3:
                    $strOrder = '季  军';
                    break;
                case 4:
                    $strOrder = '第四名';
                    break;
                case 5:
                    $strOrder = '第五名';
                    break;
                case 6:
                    $strOrder = '第六名';
                    break;
                case 7:
                    $strOrder = '第七名';
                    break;
                case 8:
                    $strOrder = '第八名';
                    break;
                case 9:
                    $strOrder = '第九名';
                    break;
                case 10:
                    $strOrder = '第十名';
                    break;
                default:
                    break;
            }
        }
        $value = '';
        if ($order['value'] == 'D') {
            $value = '单';
        } else if ($order['value'] == 'S') {
            $value = '双';
        } else if ($order['value'] == 'Z') {
            $value = '大';
        } else if ($order['value'] == 'A') {
            $value = '小';
        } else {
            $value = $order['value'];
        }
        $amount = intval($order['amount']);
        $strOrder = $strOrder . '[' . $value . '/'. strval($amount) . ']';
    }
    
    private function getUserBalance($user) {
        $userId = $user['user_id'];
        $balance = db_money_balance($userId);
        return intval($balance);
    }
    
}
