<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/funciones.php';

if (!is_google_oauth_configured()) {
    set_flash('error', 'El inicio de sesión con Google no está disponible por el momento.');
    redirect_to_app('index.php');
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'include_granted_scopes' => 'true',
    'prompt' => 'select_account',
];

redirect(GOOGLE_AUTH_ENDPOINT . '?' . http_build_query($params));
