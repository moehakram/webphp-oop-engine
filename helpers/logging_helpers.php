<?php

if (!function_exists('log_exception')) {
    function log_exception(\Throwable $ex): string
    {
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'message' => $ex->getMessage(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'trace' => $ex->getTraceAsString(),
        ];

        return sprintf(
            "[%s] Uncaught exception: %s\nIn file: %s on line %s\nStack trace:\n%s\n",
            $logData['time'],
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
     * @param string|array $message The log message
     * @param string $name The name of the logger
     * @param array $context Context array for the log message
     * @param int $level The logging level (e.g., Logger::INFO)
     * @param string $path The file path for the log
     */

    function write_log($message, $name = 'app', array $context = [], int $level = \Monolog\Level::Info): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] " . (is_array($message) ? json_encode($message, JSON_PRETTY_PRINT) : $message) . PHP_EOL;

        $logger = new \Monolog\Logger($name);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path(config('logging.info_log.path')), $level));
        $logger->log($level, $logMessage, $context);
    }
}