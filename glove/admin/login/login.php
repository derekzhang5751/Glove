<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */
require ROOT_PATH . 'Gregwar/Captcha/ImageFileHandler.php';
require ROOT_PATH . 'Gregwar/Captcha/PhraseBuilderInterface.php';
require ROOT_PATH . 'Gregwar/Captcha/PhraseBuilder.php';
require ROOT_PATH . 'Gregwar/Captcha/CaptchaBuilderInterface.php';
require ROOT_PATH . 'Gregwar/Captcha/CaptchaBuilder.php';

use Gregwar\Captcha\CaptchaBuilder;

class login extends GloveBase {
    private $action;
    private $userName;
    private $password;
    private $captchaPic;
    private $captchaText;
    
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
        $this->captchaText = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
        
        return true;
    }
    
    protected function process() {
        if ($this->action == 'login') {
            // do login
            if ($this->checkCaptcha($this->captchaText)) {
                if (empty($this->userName)) {
                    $this->return['success'] = false;
                    $this->return['msg'] = $GLOBALS['LANG']['username_empty'];
                } else if (empty($this->password)) {
                    $this->return['success'] = false;
                    $this->return['msg'] = $GLOBALS['LANG']['password_empty'];
                } else {
                    if ($this->processLogin()) {
                        $this->return['redirect'] = '/admin/main/do.php';
                    } else {
                        $this->return['success'] = false;
                    }
                }
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['captcha_error'];
            }
        }
        
        return true;
    }
    
    protected function responseWeb() {
        if ($this->action == 'login' && $this->return['success']) {
            // jump to main page
            header("Location: " . $this->return['redirect']);
            exit();
        } else {
            // generate a captcha
            $captchaBuilder = new CaptchaBuilder(4);
            $captchaBuilder->build();
            $this->captchaPic = $captchaBuilder->inline();
            $this->captchaText = $captchaBuilder->getPhrase();
            $GLOBALS['session']->setSessionData('captcha', $this->captchaText);
            // show login page
            $GLOBALS['smarty']->assign("CaptchaPic", $this->captchaPic);
            //$GLOBALS['smarty']->assign("CaptchaText", $this->captchaText);
            $GLOBALS['smarty']->assign("msg", $this->return['msg']);
            $GLOBALS['smarty']->display('login.tpl');
        }
    }
    
    private function checkCaptcha($captcha) {
        $rule = array(
            '0' => 'o',
            '1' => 'l'
        );
        $captcha1 = $GLOBALS['session']->getSessionData('captcha');
        $captcha1 = strtr(strtolower($captcha1), $rule);
        $captcha2 = strtr(strtolower($captcha), $rule);
        
        if ($captcha1 === $captcha2) {
            return true;
        } else {
            return false;
        }
    }
    
    private function processLogin() {
        $user = db_check_admin_password($this->userName, $this->password);
        if ($user) {
            $GLOBALS['session']->setSessionData('admin_id', $user['admin_id']);
            $GLOBALS['session']->setSessionData('admin_name', $user['user_name']);
            return true;
        } else {
            return false;
        }
    }
}
