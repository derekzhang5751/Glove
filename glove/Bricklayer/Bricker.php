<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */
namespace Bricker;

ini_set('date.timezone', 'Asia/Shanghai');

if (!defined('USE_BRICKER')) {
    die('Hacking attempt');
}

define('BRICKER_PATH', dirname(__FILE__));
define('ROOT_PATH', str_replace('Bricklayer/Bricker.php', '', str_replace('\\', '/', __FILE__)));
define('MODULE_PATH', ROOT_PATH . $GLOBALS['LifeCfg']['MODULE_NAME'] . '/' );

// CLIENT DEVICE TYPE
define('DEVICE_WEB',       0);
define('DEVICE_HYBRID',    1);
define('DEVICE_MOBILE',    2);


require BRICKER_PATH . '/Config.php';
require BRICKER_PATH . '/Database/Database.php';
require BRICKER_PATH . '/Log/Log.php';
require BRICKER_PATH . '/RequestLifeCircle.php';
require BRICKER_PATH . '/ISession.php';

/*
 * Init client device type
 */
$DeviceType = DEVICE_WEB;
if (isset( $_REQUEST['DeviceType'] )) {
    switch ($_REQUEST['DeviceType']) {
        case '1':
            $DeviceType = DEVICE_HYBRID;
            break;
        case '2':
            $DeviceType = DEVICE_MOBILE;
            break;
        default:
            $DeviceType = DEVICE_WEB;
            break;
    }
}

/*
 * init database
 */
$db = getDatabase();

/*
 * init log
 */
$log = getAppLog();

/*
 * auto load database assistant files
 */
$loadDbs = $GLOBALS['LifeCfg']['LOAD_DB'];
foreach ($loadDbs as $file) {
    require ROOT_PATH . 'Storage/' . $GLOBALS['LifeCfg']['DB_TYPE'] . '/' . $file . '.db.php';
}

/*
 * auto load include files
 */
$loadFiles = $GLOBALS['LifeCfg']['LOAD_LIB'];
foreach ($loadFiles as $file) {
    require ROOT_PATH . $file;
}

/*
 * Load language file
 */
require MODULE_PATH . 'Lang/' . $GLOBALS['LifeCfg']['LANG'] . '/' . $GLOBALS['LifeCfg']['MODULE_NAME'] . '.php';

/*
 * Init session
 */
if ( isset($GLOBALS['LifeCfg']['SESSION_CLASS']) ) {
    //$GLOBALS['log']->log('bricker', 'Init mobile session, Device Type: '.$DeviceType);
    //$GLOBALS['log']->log('bricker', 'POST session id = '.$_POST['SESSION_ID']);
    $autoCreateSession = isset($GLOBALS['LifeCfg']['SESSION_CREATE']) ? $GLOBALS['LifeCfg']['SESSION_CREATE'] : false;
    if ($DeviceType == DEVICE_HYBRID || $DeviceType == DEVICE_MOBILE) {
        if ( isset($_POST['SESSION_ID']) ) {
            $sessionId = $_POST['SESSION_ID'];
        } else {
            $sessionId = '';
        }
    } else {
        $sessionId = '';
    }
    //$GLOBALS['log']->log('bricker', 'Input session id = '.$sessionId);
    $MySession = new $GLOBALS['LifeCfg']['SESSION_CLASS']();
    $MySession->init($db, $sessionId, $DeviceType, $autoCreateSession);
    $GLOBALS['session'] = $MySession;
    define('SESSION_ID',    $MySession->getSessionId());
    //$GLOBALS['log']->log('bricker', 'Current session id = '.SESSION_ID);
} else {
    define('SESSION_ID', '');
}

/*
 * Init Smarty Template Engine
 */
if (DEVICE_WEB == $DeviceType) {
    require BRICKER_PATH.'/Smarty3/Smarty.class.php';
    $smarty = new \Smarty;
    $smarty->setTemplateDir( MODULE_PATH.'templates/' );
    $smarty->setCompileDir( ROOT_PATH.'templates_c/' );
    $smarty->setConfigDir( ROOT_PATH.'configs/' );
    $smarty->setCacheDir( ROOT_PATH.'cache/' );
    //$smarty->force_compile = true;
    $smarty->debugging = false;
    $smarty->caching = false;
    $smarty->cache_lifetime = 120;
}

/*
 * CORS
 */
header("Access-Control-Allow-Origin: *");

/*
 * run
 */
$className = $GLOBALS['LifeCfg']['REQUEST_NAME'];
require MODULE_PATH . $className . '/' . $className . '.php';

$request = new $className();
$request->run();

// The End
