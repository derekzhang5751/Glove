<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

class main extends GloveBase {
    private $action;
    private $userName;
    private $password;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    protected function prepareRequestParams() {
        $this->action = isset($_GET['act']) ? trim($_GET['act']) : '';
        $this->userName = isset($_POST['username']) ? trim($_POST['username']) : '';
        $this->password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        return true;
    }
    
    protected function process() {
        //
        return true;
    }
    
    protected function responseWeb() {
        $GLOBALS['smarty']->assign("UserName", $this->userName);
        switch ($this->action) {
            case 'head':
                $GLOBALS['smarty']->display('head.tpl');
                break;
            case 'menu':
                $GLOBALS['smarty']->display('left.tpl');
                break;
            case 'main':
                $GLOBALS['smarty']->display('right.tpl');
                break;
            default:
                $GLOBALS['smarty']->display('main.tpl');
                break;
        }
    }
    
}
