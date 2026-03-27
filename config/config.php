<?php
declare(strict_types=1);

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
    (string)(getenv('GOOGLE_CLIENT_ID') ?: '336324206307-83tj756f659p9oj189v38j77f6k71nt4.apps.googleusercontent.com')
);
define('GOOGLE_CLIENT_SECRET', (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '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'));
define(
    'GOOGLE_REDIRECT_URI',
    (string)(getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/sistema_practica/oauth/procesar_google.php')
);
