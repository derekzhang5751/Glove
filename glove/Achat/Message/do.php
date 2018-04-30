<?php
/**
 * User: Derek
 * Date: 2018-02-28
 * Time: 12:41 PM
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'Achat',
    'REQUEST_NAME'   => 'Message',
    'LANG'           => 'zh_cn',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Message', 'Order', 'Issue'
    ),
    'LOAD_LIB'       => array(
        'Bricklayer/Lib/network.php',
        'Common/constants.php',
        'Common/GloveBase.php',
        'Common/Command.php',
        'Common/Schedule.php'
    )
);

require '../../Bricklayer/Bricker.php';
