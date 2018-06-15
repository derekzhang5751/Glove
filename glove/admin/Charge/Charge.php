<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class Charge extends GloveBase {
    private $action;
    private $targetUserId;
    private $chargeId;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
        $this->targetUserId = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';
        $this->chargeId = isset($_REQUEST['chargeid']) ? trim($_REQUEST['chargeid']) : '';
        
        return true;
    }
    
    protected function process() {
        if ($this->action == 'list') {
            $this->loadChargeRequest();
            $this->loadMoneyCharged();
        } else if ($this->action == 'to_charge') {
            $this->loadUserChargeData();
        } else if ($this->action == 'to_withdraw') {
            $this->loadUserChargeData();
        } else if ($this->action == 'charge') {
            $this->doUserCharge();
            $this->loadChargeRequest();
            $this->loadMoneyCharged();
        } else if ($this->action == 'ignore') {
            $this->doChargeIgnore();
            $this->loadChargeRequest();
            $this->loadMoneyCharged();
        }
        
        return true;
    }
    
    protected function responseWeb() {
        switch ($this->action) {
            case 'list':
                $GLOBALS['smarty']->assign("ChargeList", $this->return['data']['ChargeList']);
                $GLOBALS['smarty']->assign("MoneyList", $this->return['data']['MoneyList']);
                $GLOBALS['smarty']->display('charge_list.tpl');
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
                $GLOBALS['smarty']->assign("ChargeList", $this->return['data']['ChargeList']);
                $GLOBALS['smarty']->assign("MoneyList", $this->return['data']['MoneyList']);
                $GLOBALS['smarty']->display('charge_list.tpl');
                break;
            case 'ignore':
                $GLOBALS['smarty']->assign("ChargeList", $this->return['data']['ChargeList']);
                $GLOBALS['smarty']->assign("MoneyList", $this->return['data']['MoneyList']);
                $GLOBALS['smarty']->display('charge_list.tpl');
                break;
            default:
                exit();
                break;
        }
    }
    
    private function loadChargeRequest() {
        $chargeList = db_get_charges_by_status(0, 10);
        if ($chargeList) {
            $this->return['data']['ChargeList'] = $chargeList;
        } else {
            $this->return['data']['ChargeList'] = array();
        }
    }
    
    private function loadMoneyCharged() {
        $moneyList = db_get_money_by_source(0, 20);
        if ($moneyList) {
            $this->return['data']['MoneyList'] = $moneyList;
        } else {
            $this->return['data']['MoneyList'] = array();
        }
    }
    
    private function loadUserChargeData() {
        $this->return['data']['user'] = array();
        
        $chargeId = intval($this->chargeId);
        $userId = intval($this->targetUserId);
        
        $charge = array();
        if ($chargeId > 0) {
            $charge = db_get_charge_by_id($chargeId);
        }
        
        if (!empty($charge)) {
            $userId = $charge['user_id'];
        }
        if ($userId <= 0) {
            return false;
        }
        
        $user = db_get_user_by_id($userId);
        if ($user) {
            $balance = $this->getUserAvailableBalance($user);
            $this->return['data']['user'] = $user;
            $this->return['data']['user']['balance'] = $balance;
            if (empty($charge)) {
                $this->return['data']['user']['amount'] = '';
                $this->return['data']['user']['sn'] = '';
            } else {
                $this->return['data']['user']['amount'] = abs($charge['amount']);
                $this->return['data']['user']['sn'] = $charge['charge_sn'];
            }
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
    
    private function doChargeIgnore() {
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
