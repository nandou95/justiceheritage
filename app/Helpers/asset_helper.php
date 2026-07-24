<?php

/**
 * Public asset URL rooted at the front controller directory (…/public/).
 * Works under XAMPP subfolders even when app.baseURL is misconfigured.
 */
if (! function_exists('public_asset')) {
    function public_asset(string $path = ''): string
    {
        $path = ltrim($path, '/');

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace(basename($script), '', $script), '/');

        if ($basePath === '' || $basePath === '/') {
            return '/' . $path;
        }

        return $basePath . '/' . $path;
    }
}
