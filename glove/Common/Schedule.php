<?php
/**
 * User: Derek
 * Date: 2018-04-29
 */
define('STEP_WELCOME',       10);
define('STEP_PK10_ORDER',    30);
define('STEP_PK10_ISSUE',    31);
define('STEP_TURN',          50);
define('STEP_XYFT_ORDER',    70);
define('STEP_XYFT_ISSUE',    71);
define('STEP_BREAK',         99);


class Schedule {
    private $curTime = '';
    private $step;
    private $issueNum;
    
    public function __construct() {
        $this->curTime = date("H:i:s");
        $this->init();
    }
    
    public function getCurStep() {
        return $this->step;
    }
    
    public function getCurIssueNum() {
        return $this->issueNum;
    }
    
    private function init() {
        $this->computeStep();
        switch ($this->step) {
            case STEP_PK10_ORDER:
            case STEP_PK10_ISSUE:
                $this->computePk10Issue();
                break;
            case STEP_XYFT_ORDER:
            case STEP_XYFT_ISSUE:
                $this->computeXyftIssue();
                break;
            default:
                break;
        }
    }
    
    private function computeStep() {
        if ($this->curTime >= '00:00:00' && $this->curTime < '00:02:00') {
            $this->step = STEP_PK10_ORDER;
        }else if ($this->curTime >= '00:02:00' && $this->curTime < '00:03:00') {
            $this->step = STEP_PK10_ISSUE;
        }else if ($this->curTime >= '00:03:00' && $this->curTime < '00:05:00') {
            $this->step = STEP_TURN;
        } else if ($this->curTime >= '00:05:00' && $this->curTime < '04:00:00') {
            $tmp = substr($this->curTime, 4);
            if ($tmp >= '0:00' && $tmp < '4:00') {
                $this->step = STEP_XYFT_ORDER;
            } else if ($tmp >= '5:00' && $tmp < '9:00') {
                $this->step = STEP_XYFT_ORDER;
            //} else if ($tmp >= '8:00') {
            //    $this->step = STEP_XYFT_ORDER;
            } else {
                $this->step = STEP_XYFT_ISSUE;
            }
        } else if ($this->curTime >= '04:03:00' && $this->curTime < '09:03:00') {
            $this->step = STEP_BREAK;
        } else if ($this->curTime >= '09:03:00' && $this->curTime < '09:08:00') {
            $this->step = STEP_WELCOME;
        } else if ($this->curTime >= '09:08:00') {
            $tmp = substr($this->curTime, 4);
            if ($tmp >= '0:00' && $tmp < '2:00') {
                $this->step = STEP_PK10_ORDER;
            } else if ($tmp >= '3:00' && $tmp < '7:00') {
                $this->step = STEP_PK10_ORDER;
            } else if ($tmp >= '8:00') {
                $this->step = STEP_PK10_ORDER;
            } else {
                $this->step = STEP_PK10_ISSUE;
            }
        } else {
            $this->step = STEP_WELCOME;
        }
    }
    
    private function computePk10Issue() {
        $issue = db_get_next_issue(0);
        if ($issue) {
            $this->issueNum = $issue['issue_num'];
        } else {
            $this->issueNum = '';
        }
    }
    
    private function computeXyftIssue() {
        $issue = db_get_next_issue(1);
        if ($issue) {
            $this->issueNum = $issue['issue_num'];
        } else {
            $this->issueNum = '';
        }
    }
}
