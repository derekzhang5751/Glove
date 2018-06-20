<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class Issue extends GloveBase {
    private $action;
    private $typeIssue;
    private $dayIssue;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
        $this->typeIssue = isset($_REQUEST['typeIssue']) ? trim($_REQUEST['typeIssue']) : '0';
        $this->dayIssue = isset($_REQUEST['dayIssue']) ? trim($_REQUEST['dayIssue']) : '';
        
        return true;
    }
    
    protected function process() {
        if ($this->action == 'list') {
            $this->getIssueList();
        } else if ($this->action == 'monitor') {
            //
        }
        return true;
    }
    
    protected function responseWeb() {
        switch ($this->action) {
            case 'list':
                $GLOBALS['smarty']->assign("arrIssueType", array(0 => 'PK10', 1 => 'XYFT'));
                $GLOBALS['smarty']->assign("IssueList", $this->return['data']['IssueList']);
                $GLOBALS['smarty']->assign("IssueDate", $this->dayIssue);
                $GLOBALS['smarty']->assign("IssueType", $this->typeIssue);
                $GLOBALS['smarty']->display('issue_list.tpl');
                break;
            default:
                exit();
                break;
        }
    }
    
    private function getIssueList() {
        $today = date("Y-m-d");
        if ($this->dayIssue == '') {
            $this->dayIssue = $today;
            $begin = $today . ' 00:00:00';
            $end   = date("Y-m-d H:i:s");
        } else {
            if ($this->dayIssue == $today) {
                $begin = $today . ' 00:00:00';
                $end   = date("Y-m-d H:i:s");
            } else {
                $begin = $this->dayIssue . ' 00:00:00';
                $end   = $this->dayIssue . ' 24:00:00';
            }
        }
        
        $type = intval($this->typeIssue);
        if ($type !== 1) {
            $type = 0;
        }
        $this->typeIssue = $type;
        
        if ($type == 1) {
            // special handle for XYFT
            $endTime = substr($end, 11);
            if ($endTime > '04:30:00') {
                $end = $this->dayIssue . ' 04:30:00';
            }
        }
        //
        $this->return['data']['IssueList'] = array();
        $issueList = db_get_issue_list($type, $begin, $end);
        if ($issueList) {
            $this->return['data']['IssueList'] = $issueList;
        }
    }
    
}
