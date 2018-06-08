<?php
/**
 * Description of MoUser
 *
 * @author Derek
 */
define('OPT_SOURCE_MANUAL',  0);
define('OPT_SOURCE_AUTO',    1);

class MoUser {
    
    public function doUserCharge($userId, $amount, $source, $sn) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return false;
        }
        $user = db_get_user_by_id($userId);
        if (!$user) {
            return false;
        }
        
        $amount = floatval($amount);
        $amount = abs($amount);
        
        $source = intval($source);
        if ($source != 1) {
            $source = 0;
        }
        
        return $this->doUserMoneyModify($user, $amount, $source, $sn);
    }
    
    public function doUserWithdraw($userId, $amount, $source, $sn) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return false;
        }
        $user = db_get_user_by_id($userId);
        if (!$user) {
            return false;
        }
        
        $balance = $this->getUserAvailableBalance($userId);
        $amount = abs( floatval($amount) );
        if ($amount > $balance) {
            return false;
        }
        
        $amount = 0.0 - abs($amount);
        
        $source = intval($source);
        if ($source != 1) {
            $source = 0;
        }
        
        return $this->doUserMoneyModify($user, $amount, $source, $sn);
    }
    
    private function doUserMoneyModify($user, $amount, $source, $sn) {
        $amount = floatval($amount);
        $balance = db_money_balance($user['user_id']);
        $newBalance = floatval($balance) + $amount;
        
        if (empty($sn) && $source == OPT_SOURCE_MANUAL) {
            $sn = $this->generate_sn();
        }
        if (empty($sn)) {
            return false;
        }
        
        $money = array(
            'user_id' => $user['user_id'],
            'user_name' => $user['user_name'],
            'amount' => $amount,
            'balance' => $newBalance,
            'source' => $source,
            'add_time' => date("Y-m-d H:i:s"),
            'status' => 0,
            'sn' => $sn,
        );
        $moneyId = db_money_insert($money);
        if ($moneyId === false) {
            return false;
        } else {
            if ($source == OPT_SOURCE_AUTO) {
                db_set_charge_status($sn, 1);
            }
            return true;
        }
    }
    
    private function generate_sn() {
        $KEY = "abcdefghijklmnopqrstuvwxyz0123456789";
        $max = strlen($KEY)-1;
        $randLen = 4;
        $orderId = date("ymdHis");
        for ($i=0; $i<$randLen; $i++) {
            $p = rand(0, $max);
            $orderId = $orderId . $KEY[$p];
        }
        return $orderId;
    }
    
    private function getUserAvailableBalance($userId) {
        $balance = db_money_balance($userId);
        $frozen = db_order_frozen_sum($userId);
        $left = floatval($balance) - floatval($frozen);
        return $left;
    }
    
}
