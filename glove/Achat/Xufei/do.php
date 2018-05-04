<?php
/**
 * User: Derek
 * Date: 2018-04-05
 * Time: 12:10 AM
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'Achat',
    'REQUEST_NAME'   => 'Xufei',
    'LANG'           => 'zh_cn',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Money'
    ),
    'LOAD_LIB'       => array(
        'Bricklayer/Lib/network.php',
        'Common/constants.php',
        'Common/GloveBase.php',
    )
);

require '../../Bricklayer/Bricker.php';
