<?php

if (!function_exists('log_exception')) {
    function log_exception(\Throwable $ex): string
    {
        $logData = [
            'message' => $ex->getMessage(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'trace' => $ex->getTraceAsString(),
        ];

        return sprintf(
            "Uncaught exception: %s\nIn file: %s on line %s\nStack trace:\n%s\n",
            $logData['message'],
            $logData['file'],
            $logData['line'],
            $logData['trace']
        );
    }
}

if (!function_exists('write_log')) {

        /**
     * Log a message using the Monolog logger.
     *
     * @param string $message The log message
     * @param array $context Context array for the log message
     * @param string $name The name of the logger
     * @param int $level The logging level
     * @param string $path The file path for the log
     */

    function write_log(string $message, array $context = [], $name = 'app', int $level = 200): void
    {
        $logger = new \Monolog\Logger($name);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path(config('logging.path')), $level));
        $logger->log($level, $message, $context);
    }
}