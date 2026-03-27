<?php
declare(strict_types=1);

if (!function_exists('load_env_file')) {
    function load_env_file(string $filePath): void
    {
        if (!is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            $isDoubleQuoted = str_starts_with($value, '"') && str_ends_with($value, '"');
            $isSingleQuoted = str_starts_with($value, "'") && str_ends_with($value, "'");
            if (($isDoubleQuoted || $isSingleQuoted) && strlen($value) >= 2) {
                $value = substr($value, 1, -1);
            }

            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

load_env_file(dirname(__DIR__) . '/.env');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Sistema Práctica');
define('BASE_URL', '/sistema_practica');

date_default_timezone_set('America/Mexico_City');

const MAX_LOGIN_ATTEMPTS = 3;
const GOOGLE_AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
const GOOGLE_USERINFO_ENDPOINT = 'https://openidconnect.googleapis.com/v1/userinfo';

define(
    'GOOGLE_CLIENT_ID',
    (string)(getenv('GOOGLE_CLIENT_ID') ?: 'TU_GOOGLE_CLIENT_ID')
);
define('GOOGLE_CLIENT_SECRET', (string)(getenv('GOOGLE_CLIENT_SECRET') ?: 'TU_GOOGLE_CLIENT_SECRET'));
define(
    'GOOGLE_REDIRECT_URI',
    (string)(getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/sistema_practica/oauth/procesar_google.php')
);
