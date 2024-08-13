<?php

if (!function_exists('base_path')) {
    function base_path($path = ''): string{
        return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . $path : DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('clean')) {
    function clean($data)
    {
        if (is_string($data)) {
            return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
        }

        if (is_array($data)) {
            return array_map('clean', $data);
        }
        return $data;
    }
}

if (!function_exists('dd')) {
    function dd($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die;
    }
}
