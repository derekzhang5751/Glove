<?php
/**
 * User: Derek
 * Date: 2018-04-05
 * Time: 12:10 AM
 */

class Xufei extends GloveBase {
    private $chargeId;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->chargeId = isset($_GET['id']) ? trim($_GET['id']) : '';
        if ( empty($this->chargeId) ) {
            return false;
        }
        
        return true;
    }
    
    protected function process() {
        //
        return true;
    }
    
    protected function responseWeb() {
        $GLOBALS['smarty']->assign("ChargeId", $this->chargeId);
        $GLOBALS['smarty']->display('Xufei.tpl');
    }
    
}
