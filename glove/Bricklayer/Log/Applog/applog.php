<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

class MyAppLog {
    private $basePath = '';
    
    public function __construct($config) {
        $this->basePath = $config['basepath'];
    }
    
    public function log($strLogType, $strMsg) {
        if (empty($strLogType) || empty($strMsg)
            || !is_string($strLogType) || !is_string($strMsg))
        {
            return;
        }
        
        // get log base path from config set
        $logBasePath = $this->basePath;
        if (empty($logBasePath) || !is_string($logBasePath)) {
            return;
        }

        // check if path exists
        $logPath = $logBasePath . $strLogType;
        if ( ! file_exists($logPath) ) {
            return;
        }

        // build log file name
        $logFilename = $logPath . "/" . $strLogType . "_" . date("Ymd") . ".log";
        $logDate = date("Y-m-d H:i:s");
        $message = "[" . $logDate . "]";

        $message = $message . $strMsg . "\r\n";
        file_put_contents($logFilename, $message, FILE_APPEND | LOCK_EX);
        //file_put_contents('/home/derekz/log/debug.txt', $logFilename, FILE_APPEND | LOCK_EX);
    }
    
}

/**
 * THE FILE ENDS
 */