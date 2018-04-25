<?php
/**
 * Author: Derek
 * Date: 2018-04-23
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'Issuer',
    'REQUEST_NAME'   => 'Issue',
    'LANG'           => 'zh_cn',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Issue'
    ),
    'LOAD_LIB'       => array(
        'Bricklayer/Lib/network.php',
        'Common/constants.php',
        'Common/GloveBase.php',
    )
);

require '../../Bricklayer/Bricker.php';
