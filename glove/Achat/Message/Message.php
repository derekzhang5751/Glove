<?php
/**
 * User: Derek
 * Date: 2018-02-28
 * Time: 12:43 PM
 */

class Message extends GloveBase {
    private $id;
    private $achat_name;
    private $group_name;
    private $from_nick;
    private $to_nick;
    private $content;
    private $recvtime;
    private $status;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $postData = parent::prepareRequestParams();
        if ($postData) {
            if ($this->version == '1.0') {
                $_POST['id']        = $postData['id'];
                $_POST['achat_name'] = $postData['achat_name'];
                $_POST['group_name'] = $postData['group_name'];
                $_POST['from_nick'] = $postData['from_nick'];
                $_POST['to_nick']   = $postData['to_nick'];
                $_POST['content']   = $postData['content'];
                $_POST['recvtime']  = $postData['recvtime'];
                $_POST['status']    = $postData['status'];
            }
        } else {
            return false;
        }
        
        $id = isset($_POST['id']) ? trim($_POST['id']) : '0';
        $this->id = intval($id);
        if ( $this->id <= 0 ) {
            return false;
        }
        
        $this->achat_name = isset($_POST['achat_name']) ? trim($_POST['achat_name']) : '';
        if ( empty($this->achat_name) ) {
            return false;
        }
        
        $this->group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';
        if ( empty($this->group_name) ) {
            return false;
        }
        
        $this->from_nick = isset($_POST['from_nick']) ? trim($_POST['from_nick']) : '';
        if ( empty($this->from_nick) ) {
            return false;
        }
        
        $this->to_nick = isset($_POST['to_nick']) ? trim($_POST['to_nick']) : '';
        /*if ( empty($this->to_nick) ) {
            exit("to_nick=".$this->to_nick);
            return false;
        }*/
        
        $this->content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if ( empty($this->content) ) {
            return false;
        }
        
        $this->recvtime = isset($_POST['recvtime']) ? trim($_POST['recvtime']) : '';
        if ( empty($this->recvtime) ) {
            return false;
        }
        
        $this->status = isset($_POST['status']) ? trim($_POST['status']) : '-1';
        $this->status = intval($this->status);
        if ( $this->status < 0 ) {
            return false;
        }
        
        return true;
    }
    
    protected function process() {
        $this->return['data']['id'] = $this->id;
        $this->return['data']['achat_name'] = $this->achat_name;
        $this->return['data']['group_name'] = $this->group_name;
        $this->return['data']['from_nick'] = $this->from_nick;
        $this->return['data']['to_nick'] = $this->to_nick;
        $this->return['data']['content'] = $this->content;
        $this->return['data']['recvtime'] = $this->recvtime;
        $this->return['data']['reply'] = '';
        $this->return['data']['sendtime'] = '';
        $this->return['data']['status'] = $this->status;
        
        // First, parse command from messasge
        $msg = $this->content;
        $cmdArr = $this->stringToArray($msg);
        
        $cmd = $this->parseCommandFromMsg($cmdArr);
        if (COMMAND_INVALID == $cmd) {
            $this->fillWithInvalidCmd();
        } else {
            $this->fillWithSuccessCmd();
            switch ($cmd) {
                case COMMAND_CHARGE:
                case COMMAND_REGISTER:
                    $this->processCharge($cmdArr);
                    break;
                case COMMAND_ORDER:
                    $this->processOrder($cmdArr);
                    break;
                case COMMAND_BALANCE:
                    $this->processBalance($cmdArr);
                    break;
                case COMMAND_WITHDRAW:
                    $this->processWithdraw($cmdArr);
                    break;
                default:
                    break;
            }
        }
        $this->return['data']['sendtime'] = date("Y-m-d H:i:s");
        
        // Save message to database
        db_message_insert($this->return['data']);
        
        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    private function fillWithInvalidCmd() {
        $i = rand(0, 2);
        $this->return['data']['reply'] = $GLOBALS['LANG']['cmd_invalid'][$i];
        $this->return['data']['status'] = COMMAND_INVALID;
    }
    
    private function fillWithSuccessCmd() {
        $i = rand(0, 2);
        $this->return['data']['reply'] = $GLOBALS['LANG']['cmd_success'][$i];
        $this->return['data']['status'] = COMMAND_SUCCESS;
    }
    
    private function fillWithFailedCmd() {
        $i = 0;
        $this->return['data']['reply'] = $GLOBALS['LANG']['cmd_failed'][$i];
        $this->return['data']['status'] = COMMAND_FAILED;
    }
    
    private function parseCommandFromMsg($cmdArr) {
        if (count($cmdArr) < 1) {
            return COMMAND_INVALID;
        }
        $cmd = $cmdArr[0];
        if (in_array($cmd, $GLOBALS['LANG']['cmd_array_charge'])) {
            return COMMAND_CHARGE;
        } else if (in_array($cmd, $GLOBALS['LANG']['cmd_array_order'])) {
            return COMMAND_ORDER;
        } else if (in_array($cmd, $GLOBALS['LANG']['cmd_array_balance'])) {
            return COMMAND_BALANCE;
        } else if (in_array($cmd, $GLOBALS['LANG']['cmd_array_withdraw'])) {
            return COMMAND_WITHDRAW;
        } else {
            return COMMAND_INVALID;
        }
    }
    
    private function loadUser($autoAdd = false) {
        // user_id, user_name, password, reg_time, last_time
        // read user from db, if it's not exist, then create it.
        $user = db_get_user($this->from_nick);
        if (!$user) {
            if ($autoAdd) {
                // create a new user account
                $cur_time = date("Y-m-d H:i:s");
                $user = array(
                    'user_id'    => 0,
                    'user_name'  => $this->from_nick,
                    'password'   => '',
                    'reg_time'   => $cur_time,
                    'last_time'  => $cur_time,
                    'achat_name' => $this->achat_name,
                    'group_name' => $this->group_name,
                );
                $user['user_id'] = db_user_insert($user);
                if ($user['user_id'] == false) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return $user;
    }
    
    private function processCharge($cmdArr) {
        if (count($cmdArr) < 2 || floatval($cmdArr[1]) <= 0.0) {
            $this->fillWithInvalidCmd();
            return false;
        }
        // user name and id, amount, req_time, status
        $user = $this->loadUser(true);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_register'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        else
        {
            $money = array(
                'user_id'    => $user['user_id'],
                'user_name'  => $user['user_name'],
                'amount'     => floatval($cmdArr[1]),
                'req_source' => 0,
                'req_time'   => date("Y-m-d H:i:s"),
                'status'     => 0,
                'charge_id'  => Message::generate_order_id()
            );
            $moneyId = db_money_insert($money);
            if ($moneyId) {
                $reply = $GLOBALS['LANG']['msg_charge'];
                $reply = $reply . 'http://' . $_SERVER['SERVER_NAME'] . '/Achat/Xufei/do.php?id=' . $money['charge_id'];
                $this->return['data']['reply'] = $reply;
                $this->return['data']['status'] = COMMAND_SUCCESS;
            } else {
                $this->fillWithFailedCmd();
            }
        }
        return true;
    }
    
    private function processOrder($cmdArr) {
        $user = $this->loadUser(false);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_register'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        return true;
    }
    
    private function processBalance($cmdArr) {
        $user = $this->loadUser(false);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_register'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        $balance = $this->getUserBalance($user);
        $reply = '余额：' . $balance;
        $this->return['data']['reply'] = $reply;
        $this->return['data']['status'] = COMMAND_SUCCESS;
        
        return true;
    }
    
    private function processWithdraw($cmdArr) {
        $user = $this->loadUser(false);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_not_member'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        $amount = 0.0;
        if (count($cmdArr) >= 2) {
            $amount = floatval($cmdArr[1]);
            $amount = abs($amount);
        }
        
        $balance = $this->getUserBalance($user);
        $balance = floatval($balance);
        if ($amount > 0.0) {
            if ($amount > $balance) {
                $this->return['data']['reply'] = $GLOBALS['LANG']['error_withdraw'];
                $this->return['data']['status'] = COMMAND_FAILED;
                return false;
            }
        } else {
            $amount = $balance;
        }
        
        // Withdraw money
        $amount = 0.0 - $amount;
        $money = array(
            'user_id'    => $user['user_id'],
            'user_name'  => $user['user_name'],
            'amount'     => $amount,
            'reg_source' => 0,
            'req_time'   => date("Y-m-d H:i:s"),
            'status'     => 0,
            'charge_id'  => Message::generate_order_id()
        );
        $moneyId = db_money_insert($money);
        if ($moneyId) {
            $reply = $GLOBALS['LANG']['msg_withdraw'];
            $reply = $reply . 'http://' . $_SERVER['SERVER_NAME'] . '/Achat/Xufei/do.php?id=' . $money['charge_id'];
            $this->return['data']['reply'] = $reply;
            $this->return['data']['status'] = COMMAND_SUCCESS;
        } else {
            $this->fillWithFailedCmd();
        }
        
        return true;
    }
    
    private function stringToArray($str) {
        $a = array();
        $tok = strtok($str, ", /");
        while ($tok !== false) {
            $s = trim($tok);
            if ( !empty($s) ) {
                array_push($a, $s);
            }
            $tok = strtok(", /");
        }
        return $a;
    }
    
    private function getUserBalance($user) {
        $userId = $user['user_id'];
        $balance = db_money_balance($userId);
        $balance = floatval($balance);
        $format_num = sprintf("%.2f", $balance);
        return $format_num;
    }
    
    public static function generate_order_id() {
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
    
}