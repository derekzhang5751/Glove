<?php
/**
 * Author: Derek
 * Date: 2018-04-23
 */

class Issue extends GloveBase {
    private $action;
    private $type;
    private $issueNum;
    private $issueTime;
    private $n0, $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9;
    
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
                $_POST['act'] = $postData['act'];
            }
        } else {
            return false;
        }
        
        $this->action = isset($_POST['act']) ? trim($_POST['act']) : '';
        if ( empty($this->action) ) {
            return false;
        }
        
        $this->type = isset($postData['type']) ? trim($postData['type']) : '';
        $this->issueNum = isset($postData['issueNum']) ? trim($postData['issueNum']) : '';
        $this->issueTime = isset($postData['issueTime']) ? trim($postData['issueTime']) : '';
        $this->n0 = isset($postData['n0']) ? trim($postData['n0']) : '';
        $this->n1 = isset($postData['n1']) ? trim($postData['n1']) : '';
        $this->n2 = isset($postData['n2']) ? trim($postData['n2']) : '';
        $this->n3 = isset($postData['n3']) ? trim($postData['n3']) : '';
        $this->n4 = isset($postData['n4']) ? trim($postData['n4']) : '';
        $this->n5 = isset($postData['n5']) ? trim($postData['n5']) : '';
        $this->n6 = isset($postData['n6']) ? trim($postData['n6']) : '';
        $this->n7 = isset($postData['n7']) ? trim($postData['n7']) : '';
        $this->n8 = isset($postData['n8']) ? trim($postData['n8']) : '';
        $this->n9 = isset($postData['n9']) ? trim($postData['n9']) : '';
        
        return true;
    }
    
    protected function process() {
        $this->return['data']['act'] = $this->action;
        
        if ($this->action == 'issue') {
            $this->processIssue();
        } else if ($this->action == 'init') {
            $this->processInitIssuer();
        }
        
        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    private function processInitIssuer() {
        $type = $this->getCurrentLotteryType();
        // Check current issue term
        $curDate = date("Y-m-d");
        $curTime = intval( date("Hi") );
        if ($curTime >= 0 && $curTime < 430) {
            $tmpDate = $curDate . " 00:10:00";
            $curDate = date("Y-m-d", strtotime($tmpDate . " -1 day"));
        }
        $begin = $curDate . " 23:00:00";
        $end   = $curDate . " 23:59:59";
        //$success = false;
        
        $termInitSize = db_get_issue_count($type, $begin, $end);
        if ($termInitSize <= 0) {
            // initialize all terms of current day
            $this->initPk10($curDate);
            $this->initXyft($curDate);
            $this->return['data']['lastIssueNum'] = 0;
            $this->return['data']['lastIssueTime'] = '';
        }
        
        $lastIssue = db_get_last_issue($type);
        if ($lastIssue) {
            //$this->return['msg'] = 'SQL:' . $GLOBALS['db']->last();
            $this->return['data']['lastIssueNum'] = $lastIssue['issue_num'];
            $this->return['data']['lastIssueTime'] = $lastIssue['issue_time'];
        } else {
            $this->return['data']['lastIssueNum'] = 0;
            $this->return['data']['lastIssueTime'] = '';
        }
        $success = true;
        
        $this->return['success'] = $success;
        if ($success) {
            $this->return['data']['curLotteryType'] = $type;
            
            $nextIssue = db_get_next_issue($type);
            if ($nextIssue) {
                //$this->return['msg'] = 'SQL:' . $GLOBALS['db']->last();
                $this->return['data']['nextIssueNum'] = $nextIssue['issue_num'];
                $this->return['data']['nextIssueTime'] = $nextIssue['issue_time'];
            } else {
                $this->return['success'] = false;
            }
        } else {
            $this->return['success'] = $success;
        }
        
        $this->return['data']['nextLaunch'] = $this->getWaitMinutes();
        return true;
    }
    
    private function processIssue() {
        if ( !$this->checkIssueParams() ) {
            return false;
        }
        
        $type = $this->getCurrentLotteryType();
        
        if ($this->issueNum > 0) {
            $arrayData = array(
                'n0' => $this->n0,
                'n1' => $this->n1,
                'n2' => $this->n2,
                'n3' => $this->n3,
                'n4' => $this->n4,
                'n5' => $this->n5,
                'n6' => $this->n6,
                'n7' => $this->n7,
                'n8' => $this->n8,
                'n9' => $this->n9,
                'status' => 1,
                'real_time' => $this->issueTime,
            );
            $success = db_update_issue($type, $this->issueNum, $arrayData);
            $this->return['success'] = $success;
            if ($success) {
                $this->awards($this->issueNum, $arrayData);
            } else {
                //$this->return['msg'] = $GLOBALS['db']->error();
                $this->return['msg'] = $GLOBALS['db']->last();
            }
        } else {
            $this->return['success'] = true;
        }
        
        $nextIssue = db_get_next_issue($type);
        if ($nextIssue) {
            $this->return['data']['nextIssueType'] = $type;
            $this->return['data']['nextIssueNum'] = $nextIssue['issue_num'];
            $this->return['data']['nextIssueTime'] = $nextIssue['issue_time'];
        } else {
            // Maybe lottery type changed, check it out
            $curTime = intval( date("Hi") );
            if ($curTime > 0 && $curTime < 430) {
                // initialize next day issues
                $initDate = date("Y-m-d");
                $this->initPk10($initDate);
                $this->initXyft($initDate);
            }
            // retry to get next issue
            $type = $this->switchLotteryType($type);
            $nextIssue = db_get_next_issue($type);
            if ($nextIssue) {
                $this->return['data']['nextIssueType'] = $type;
                $this->return['data']['nextIssueNum'] = $nextIssue['issue_num'];
                $this->return['data']['nextIssueTime'] = $nextIssue['issue_time'];
            } else {
                $this->return['data']['nextIssueType'] = $type;
                $this->return['data']['nextIssueNum'] = 0;
                $this->return['data']['nextIssueTime'] = '';
            }
        }
        
        $this->return['data']['nextLaunch'] = $this->getWaitMinutes();
        return true;
    }
    
    private function switchLotteryType($type) {
        if (LOTTERY_PK10 == $type) {
            return LOTTERY_XYFT;
        } else {
            return LOTTERY_PK10;
        }
    }
    
    private function getCurrentLotteryType() {
        $curTime = intval( date("Hi") );
        if ($curTime > 3 && $curTime < 430) {
            return LOTTERY_XYFT;
        } else {
            return LOTTERY_PK10;
        }
    }
    
    private function initPk10($date) {
        $begin = $date . " 09:00:00";
        $end = $date . " 23:59:59";
        $allData = array();
        $tmp = date("Y-m-d H:i:s", strtotime($begin . " +5 minute"));
        
        $issueNum = $this->computePk10IssueNum($date);
        if (!$issueNum) {
            return false;
        }
        $issueNum = intval($issueNum);
        while ($tmp <= $end) {
            $data = array(
                'type' => LOTTERY_PK10,
                'issue_num' => $issueNum,
                'n0' => 0,
                'n1' => 0,
                'n2' => 0,
                'n3' => 0,
                'n4' => 0,
                'n5' => 0,
                'n6' => 0,
                'n7' => 0,
                'n8' => 0,
                'n9' => 0,
                'status' => 0,
                'issue_time' => $tmp,
            );
            array_push($allData, $data);
            //
            $tmp = date("Y-m-d H:i:s", strtotime($tmp . " +5 minute"));
            $issueNum = $issueNum + 1;
        }
        if ($allData) {
            return db_insert_issues($allData);
        } else {
            return false;
        }
    }
    private function computePk10IssueNum($date) {
        $stdDate = '2018-04-24';
        $stdNum = 678198;
        $d1 = strtotime($stdDate);
        $d2 = strtotime($date);
        if ($d2 > $d1) {
            $days = round(($d2 - $d1) / 3600 / 24);
            return $stdNum + ($days * 179);
        } else {
            return false;
        }
    }
    
    private function initXyft($curDate) {
        $begin = $curDate . " 13:00:00";
        $end = $curDate . " 23:59:59";
        $date = date("ymd", strtotime($begin));
        $allData = array();
        $tmp = date("Y-m-d H:i:s", strtotime($begin . " +5 minute"));
        $index = 1;
        while ($tmp <= $end) {
            $issueNum = sprintf("%s%03d", $date, $index);
            $index = $index + 1;
            $data = array(
                'type' => LOTTERY_XYFT,
                'issue_num' => $issueNum,
                'n0' => 0,
                'n1' => 0,
                'n2' => 0,
                'n3' => 0,
                'n4' => 0,
                'n5' => 0,
                'n6' => 0,
                'n7' => 0,
                'n8' => 0,
                'n9' => 0,
                'status' => 0,
                'issue_time' => $tmp,
            );
            array_push($allData, $data);
            //
            $tmp = date("Y-m-d H:i:s", strtotime($tmp . " +5 minute"));
        }
        // The second day
        $date = date("ymd", strtotime($begin));
        $nextDay = date("Y-m-d", strtotime($end . " +60 minute"));
        $begin = $nextDay . " 00:00:00";
        $end = $nextDay . " 03:59:59";
        $tmp = date("Y-m-d H:i:s", strtotime($begin . " +5 minute"));
        while ($tmp <= $end) {
            $issueNum = sprintf("%s%03d", $date, $index);
            $index = $index + 1;
            $data = array(
                'type' => LOTTERY_XYFT,
                'issue_num' => $issueNum,
                'n0' => 0,
                'n1' => 0,
                'n2' => 0,
                'n3' => 0,
                'n4' => 0,
                'n5' => 0,
                'n6' => 0,
                'n7' => 0,
                'n8' => 0,
                'n9' => 0,
                'status' => 0,
                'issue_time' => $tmp,
            );
            array_push($allData, $data);
            //
            $tmp = date("Y-m-d H:i:s", strtotime($tmp . " +5 minute"));
        }
        // Save
        if ($allData) {
            return db_insert_issues($allData);
        } else {
            return false;
        }
    }
    
    private function checkIssueParams() {
        $this->type = intval($this->type);
        if ( $this->type < 0 || $this->type > 1 ) {
            return false;
        }
        
        if (strlen($this->issueNum) > 9) {
            $this->issueNum = substr($this->issueNum, -9);
        }
        $this->issueNum = intval($this->issueNum);
        if ( $this->issueNum <= 0 ) {
            return true;
        }
        
        if ( empty($this->issueTime) ) {
            return false;
        }
        
        if ( empty($this->n0) ) {
            return false;
        }
        $this->n0 = intval($this->n0);
        if ($this->n0 < 1 || $this->n0 > 10) {
            return false;
        }
        
        if ( empty($this->n1) ) {
            return false;
        }
        $this->n1 = intval($this->n1);
        if ($this->n1 < 1 || $this->n1 > 10) {
            return false;
        }
        
        if ( empty($this->n2) ) {
            return false;
        }
        $this->n2 = intval($this->n2);
        if ($this->n2 < 1 || $this->n2 > 10) {
            return false;
        }
        
        if ( empty($this->n3) ) {
            return false;
        }
        $this->n3 = intval($this->n3);
        if ($this->n3 < 1 || $this->n3 > 10) {
            return false;
        }
        
        if ( empty($this->n4) ) {
            return false;
        }
        $this->n4 = intval($this->n4);
        if ($this->n4 < 1 || $this->n4 > 10) {
            return false;
        }
        
        if ( empty($this->n5) ) {
            return false;
        }
        $this->n5 = intval($this->n5);
        if ($this->n5 < 1 || $this->n5 > 10) {
            return false;
        }
        
        if ( empty($this->n6) ) {
            return false;
        }
        $this->n6 = intval($this->n6);
        if ($this->n6 < 1 || $this->n6 > 10) {
            return false;
        }
        
        if ( empty($this->n7) ) {
            return false;
        }
        $this->n7 = intval($this->n7);
        if ($this->n7 < 1 || $this->n7 > 10) {
            return false;
        }
        
        if ( empty($this->n8) ) {
            return false;
        }
        $this->n8 = intval($this->n8);
        if ($this->n8 < 1 || $this->n8 > 10) {
            return false;
        }
        
        if ( empty($this->n9) ) {
            return false;
        }
        $this->n9 = intval($this->n9);
        if ($this->n9 < 1 || $this->n9 > 10) {
            return false;
        }
        
        return true;
    }
    
    private function getWaitMinutes() {
        $default = 5;
        $nextTime = '';
        
        if (isset($this->return['data']['nextIssueTime'])) {
            $nextTime = $this->return['data']['nextIssueTime'];
        }
        
        if (strlen($nextTime) != strlen('2018-10-10 10:10:10')) {
            return $default;
        }
        
        $beginTime = time();
        $endTime = strtotime($nextTime);
        
        if ($endTime > $beginTime) {
            $diff = $endTime - $beginTime;
            $wait = $diff / 60;
        } else {
            $wait = 0;
        }
        
        return $wait;
    }
    
    private function awards($issueNum, $issueData=False) {
        // prepare issue data
        if (!$issueData) {
            $issueData = db_get_issue_data($issueNum);
        }
        if ($issueData) {
            if ($issueData['status'] != 1) {
                return false;
            }
            if ($issueData['n0'] == 0 && $issueData['n1'] == 0) {
                return false;
            }
        } else {
            return false;
        }
        //
        $updateSize = 0;
        $times = 0;
        do {
            $updateSize = $this->dispatchAward($issueNum, $issueData);
            $times = $times + 1;
            if ($times > 100) {
                $updateSize = 0;
                break;
            }
        } while ($updateSize > 0);
        return true;
    }
    
    private function dispatchAward($issueNum, $issueData) {
        $updateSize = 0;
        $orders = db_get_order_new($issueNum, 100);
        if ($orders) {
            foreach ($orders as $order) {
                $bingo = $this->isBingo($order, $issueData);
                // update order status
                if ($bingo) {
                    db_update_order_status($order['order_id'], 2);
                    $amount = $order['amount'];
                } else {
                    db_update_order_status($order['order_id'], 1);
                    $amount = 0 - $order['amount'];
                }
                // update user money balance
                $balance = db_money_balance( $order['user_id'] );
                $balance = floatval($balance);
                $balance = $balance + $amount;
                $money = array(
                    'user_id'    => $order['user_id'],
                    'user_name'  => $order['user_name'],
                    'amount'     => $amount,
                    'balance'  => $balance,
                    'source'  => 1,
                    'add_time'   => $money['add_time'],
                    'status'     => 0,
                    'sn'     => $order['order_sn'],
                );
                db_money_insert($money);
                $updateSize = $updateSize + 1;
            }
        } else {
            $updateSize = 0;
        }
        return $updateSize;
    }
    
    private function isBingo($order, $issue) {
        // do something
        return true;
    }
}
