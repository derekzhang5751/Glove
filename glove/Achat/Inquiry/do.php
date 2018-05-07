<?php
/**
 * User: Derek
 * Date: 2018-05-07
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'Achat',
    'REQUEST_NAME'   => 'Inquiry',
    'LANG'           => 'zh_cn',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Order', 'Issue', 'Money', 'User'
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
