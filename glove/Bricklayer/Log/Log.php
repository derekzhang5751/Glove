<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

require BRICKER_PATH . '/Log/Applog/applog.php';

$gAppLog = null;

function getAppLog() {
    global $gAppLog;
    
    if ($gAppLog) {
        return $gAppLog;
    } else {
        global $gConfig;
        $gAppLog = new MyAppLog($gConfig['log']);
    }
    
    return $gAppLog;
}
