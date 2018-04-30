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
    
    public function __construct() {
        //
    }
    
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
        $this->return['data']['cmd'] = '';
        $this->return['data']['recvtime'] = $this->recvtime;
        $this->return['data']['reply'] = '';
        $this->return['data']['sendtime'] = '';
        $this->return['data']['status'] = $this->status;
        $this->return['data']['link_id'] = '';
        
        // First, parse command from messasge
        $msg = $this->content;
        $command = new Command($msg);
        $this->return['data']['cmd'] = $command->getCmdFormatted();
        $this->return['data']['sendtime'] = date("Y-m-d H:i:s");
        
        $schedule = new Schedule();
        
        $cmdType = $command->getCmdType();
        if (COMMAND_INVALID == $cmdType) {
            $this->fillWithInvalidCmd();
        } else {
            $this->fillWithSuccessCmd();
            switch ($cmdType) {
                case COMMAND_CHARGE:
                case COMMAND_REGISTER:
                    $this->processCharge($command);
                    break;
                case COMMAND_ORDER:
                    $this->processOrder($command, $schedule);
                    break;
                case COMMAND_BALANCE:
                    $this->processBalance($command);
                    break;
                case COMMAND_WITHDRAW:
                    $this->processWithdraw($command);
                    break;
                default:
                    break;
            }
        }
        
        // Save message to database
        $msgId = db_message_insert($this->return['data']);
        if ($msgId === false) {
            return false;
        }
        
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
    
    private function processCharge($command) {
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
                'charge_id'  => Message::generate_order_id(),
                'user_id'    => $user['user_id'],
                'user_name'  => $user['user_name'],
                'amount'     => $command->getAmount(),
                'status'     => 0,
                'req_time'   => date("Y-m-d H:i:s"),
            );
            $moneyId = db_charge_insert($money);
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
    
    private function processOrder($command, $schedule) {
        $user = $this->loadUser(false);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_register'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        $step = $schedule->getCurStep();
        $issueNum = $schedule->getCurIssueNum();
        
        if ($step != STEP_PK10_ORDER && $step != STEP_XYFT_ORDER) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_break_time'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        if ($command->getAmount() < 5.0) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_amount_low'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        if ($command->getAmount() > 50000.0) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_amount_high'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        $balance = $this->getUserBalance($user);
        $balance = floatval($balance);
        if ($command->getAmount() > $balance) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['e_money_not_enough'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        if ($command->getCmdType() == COMMAND_ORDER) {
            $orderSn = Message::generate_order_id();
            $order = array(
                'order_sn' => $orderSn,
                'issue_num' => $issueNum,
                'line' => $command->getLineNum(),
                'value' => $command->getOrderValue(),
                'amount' => $command->getAmount(),
                'status' => 0,
            );
            $orderId = db_order_insert($order);
            if ($orderId === false) {
                $this->return['data']['reply'] = $GLOBALS['LANG']['error_order'];
                $this->return['data']['status'] = COMMAND_FAILED;
                return false;
            }
            $this->return['data']['link_id'] = $orderSn;
        }
        
        return true;
    }
    
    private function processBalance($command) {
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
    
    private function processWithdraw($command) {
        $user = $this->loadUser(false);
        if ($user == false) {
            $this->return['data']['reply'] = $GLOBALS['LANG']['error_not_member'];
            $this->return['data']['status'] = COMMAND_FAILED;
            return false;
        }
        
        $amount = $command->getAmount();
        if ($amount) {
            $amount = floatval($amount);
            $amount = abs($amount);
        } else {
            $amount = 0.0;
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
            'charge_id'  => Message::generate_order_id(),
            'user_id'    => $user['user_id'],
            'user_name'  => $user['user_name'],
            'amount'     => $amount,
            'status'     => 0,
            'req_time'   => date("Y-m-d H:i:s"),
        );
        $moneyId = db_charge_insert($money);
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
