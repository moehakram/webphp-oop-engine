<?php

if (!function_exists('log_exception')) {
    function log_exception(\Throwable $ex): void
    {
        $time = date('Y-m-d H:i:s');
        $message = "[{$time}] Uncaught exception: " . $ex->getMessage() . "\n";
        $message .= "In file: " . $ex->getFile() . " on line " . $ex->getLine() . "\n";
        $message .= "Stack trace:\n" . $ex->getTraceAsString() . "\n";
        // error_log($message, 3, base_path('logs/error.log'));
        error_log($message, 3, config('logging.error_log.path'));
    }
}

if (!function_exists('write_log')) {
    function write_log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] " . (is_array($message) ? json_encode($message) : $message) . PHP_EOL;
        file_put_contents(config('logging.info_log.path'), $logMessage, FILE_APPEND);
    }
}