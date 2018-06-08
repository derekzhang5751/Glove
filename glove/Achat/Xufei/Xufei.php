<?php
/**
 * User: Derek
 * Date: 2018-04-05
 * Time: 12:10 AM
 */

class Xufei extends GloveBase {
    private $chargeId;
    private $chargeAmount;
    private $userName;
    private $msg;
    
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
        $this->chargeId = isset($_GET['id']) ? trim($_GET['id']) : '';
        if ( empty($this->chargeId) ) {
            return false;
        }
        
        return true;
    }
    
    protected function process() {
        // read record of charge
        $charge = db_get_charge_by_sn($this->chargeId);
        if ($charge) {
            /*
            $this->chargeAmount = $charge['amount'];
            $this->userName = $charge['user_name'];
            
            $balance = db_money_balance($charge['user_id']);
            $balance = floatval($balance) + floatval($charge['amount']);
            
            $money = array(
                'user_id' => $charge['user_id'],
                'user_name' => $charge['user_name'],
                'amount' => $charge['amount'],
                'balance' => $balance,
                'source' => 0,
                'add_time' => date("Y-m-d H:i:s"),
                'status' => 0,
                'sn' => $charge['charge_sn'],
            );
            $moneyId = db_money_insert($money);
             */
            $moUser = new MoUser();
            $ret = $moUser->doUserCharge($charge['user_id'], $charge['amount'], OPT_SOURCE_AUTO, $charge['charge_sn']);
            if ($ret === false) {
                $this->msg = "充值失败";
            } else {
                //db_set_charge_status($charge['charge_sn'], 1);
                $this->msg = "充值成功";
            }
        } else {
            $this->msg = "订单不存在";
        }
        return true;
    }
    
    protected function responseWeb() {
        $GLOBALS['smarty']->assign("ChargeId", $this->chargeId);
        $GLOBALS['smarty']->assign("ChargeAmount", $this->chargeAmount);
        $GLOBALS['smarty']->assign("UserName", $this->userName);
        $GLOBALS['smarty']->assign("Message", $this->msg);
        $GLOBALS['smarty']->display('Xufei.tpl');
    }
    
}
