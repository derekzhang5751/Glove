<?php
/**
 * User: Derek
 * Date: 2018-06-20
 */
define('USE_BRICKER', true);

$LifeCfg = array(
    'MODULE_NAME'    => 'Achat',
    'REQUEST_NAME'   => 'Guide',
    'LANG'           => 'zh_cn',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Guide', 'Issue'
    ),
    'LOAD_LIB'       => array(
        'Bricklayer/Lib/network.php',
        'Common/constants.php',
        'Common/GloveBase.php',
        'Common/Schedule.php'
    )
);

require '../../Bricklayer/Bricker.php';
