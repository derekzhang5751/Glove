<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

require BRICKER_PATH . '/Database/Medoo/Medoo.php';

use Medoo\Medoo;

$gDbConnector = null;

function getDatabase() {
    global $gDbConnector, $gConfig;
    
    if ($gDbConnector) {
        return $gDbConnector;
    } else {
        $gDbConnector = new Medoo($gConfig['db']);
    }
    
    return $gDbConnector;
}
