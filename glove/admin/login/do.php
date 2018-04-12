<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'admin',
    'REQUEST_NAME'   => 'login',
    'LANG'           => 'zh_cn',
    'SESSION_CLASS'  => 'GloveSession',
    'SESSION_CREATE' => true,
    'ADMIN_LEVEL'    => 0,
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'User'
    ),
    'LOAD_LIB'       => array(
        'Bricklayer/Lib/network.php',
        'Common/constants.php',
        'Common/GloveBase.php',
        'Common/GloveSession.php'
    )
);

require '../../Bricklayer/Bricker.php';
