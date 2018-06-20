<?php
/**
 * User: Derek
 * Date: 2018-06-20
 */

class Guide extends GloveBase {
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    private $preOrders = array(
        '单100', '双100', '大100', '小100'
    );
    
    public function __construct() {
        //
    }
    
    protected function prepareRequestParams() {
        return true;
    }
    
    protected function process() {
        $this->getGuideOrder();
        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    private function getGuideOrder() {
        $schedule = new Schedule();
        $issueNum = $schedule->getCurIssueNum();
        $guide = db_get_guide_flag($issueNum);
        $arrFlag = array();
        $newGuide = true;
        if ($guide) {
            $newGuide = false;
            $remark = $guide['remark'];
            if ($remark) {
                $arrFlag = explode(',', $remark);
            }
        }
        
        $newFlag = '';
        $max = count($this->preOrders) - 1;
        for ($i=0; $i<20; $i++) {
            $flag = strval( rand(0, $max) );
            if (!in_array($flag, $arrFlag)) {
                $newFlag = $flag;
                array_push($arrFlag, $flag);
                break;
            }
        }
        
        if ($newFlag == '') {
            $this->return['success'] = false;
        } else {
            $index = intval($newFlag);
            $this->return['success'] = true;
            $this->return['data']['issue'] = $issueNum;
            $this->return['data']['order'] = $this->preOrders[$index];
            
            $flagStr = implode(',', $arrFlag);
            if ($newGuide) {
                $arrayData = array(
                    'issue_num' => $issueNum,
                    'remark' => $flagStr
                );
                db_insert_guide($arrayData);
            } else {
                $arrayData = array(
                    'remark' => $flagStr
                );
                db_update_guide($issueNum, $arrayData);
            }
        }
    }
    
}
