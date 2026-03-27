<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/autenticacion.php';

/**
 * @return array{ok: bool, status: int, json: array<string, mixed>|null, error: string|null}
 */
function request_json_with_curl(string $url, string $method, array $headers = [], ?array $postData = null): array
{
    $ch = curl_init($url);

    if ($ch === false) {
        return [
            'ok' => false,
            'status' => 0,
            'json' => null,
            'error' => 'No fue posible iniciar la conexión cURL.',
        ];
    }

    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
    ];

    if ($headers !== []) {
        $curlOptions[CURLOPT_HTTPHEADER] = $headers;
    }

    if (strtoupper($method) === 'POST') {
        $curlOptions[CURLOPT_POST] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($postData ?? []);
    }

    curl_setopt_array($ch, $curlOptions);

    $rawResponse = curl_exec($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($rawResponse === false) {
        return [
            'ok' => false,
            'status' => $statusCode,
            'json' => null,
            'error' => $curlError !== '' ? $curlError : 'Error de conexión con el proveedor OAuth.',
        ];
    }

    $decoded = json_decode((string)$rawResponse, true);

    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'status' => $statusCode,
            'json' => null,
            'error' => 'La respuesta del proveedor OAuth no es JSON válido.',
        ];
    }

    $isOk = $statusCode >= 200 && $statusCode < 300;
    $errorMessage = null;

    if (!$isOk) {
        $errorMessage = (string)($decoded['error_description'] ?? $decoded['error'] ?? 'Error al consultar Google OAuth.');
    }

    return [
        'ok' => $isOk,
        'status' => $statusCode,
        'json' => $decoded,
        'error' => $errorMessage,
    ];
}

if (!is_google_oauth_configured()) {
    set_flash('error', 'El inicio de sesión con Google no está disponible por el momento.');
    redirect_to_app('index.php');
}

$oauthState = (string)($_GET['state'] ?? '');
$sessionState = (string)($_SESSION['oauth_state'] ?? '');
unset($_SESSION['oauth_state']);

if ($oauthState === '' || $sessionState === '' || !hash_equals($sessionState, $oauthState)) {
    set_flash('error', 'No se pudo validar el inicio de sesión. Intenta nuevamente.');
    redirect_to_app('index.php');
}

$code = trim((string)($_GET['code'] ?? ''));
if ($code === '') {
    set_flash('error', 'No se pudo completar el inicio de sesión con Google.');
    redirect_to_app('index.php');
}

$tokenResponse = request_json_with_curl(
    GOOGLE_TOKEN_ENDPOINT,
    'POST',
    ['Accept: application/json'],
    [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI,
    ]
);

if (!$tokenResponse['ok'] || !is_array($tokenResponse['json'])) {
    set_flash('error', 'No se pudo completar el inicio de sesión con Google. Intenta más tarde.');
    redirect_to_app('index.php');
}

$accessToken = (string)($tokenResponse['json']['access_token'] ?? '');
if ($accessToken === '') {
    set_flash('error', 'No se pudo completar el inicio de sesión con Google. Intenta más tarde.');
    redirect_to_app('index.php');
}

$profileResponse = request_json_with_curl(
    GOOGLE_USERINFO_ENDPOINT,
    'GET',
    [
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ]
);

if (!$profileResponse['ok'] || !is_array($profileResponse['json'])) {
    set_flash('error', 'No se pudo completar el inicio de sesión con Google. Intenta más tarde.');
    redirect_to_app('index.php');
}

$googleProfile = $profileResponse['json'];
$email = trim((string)($googleProfile['email'] ?? ''));
$displayName = trim((string)($googleProfile['name'] ?? ''));
$emailVerifiedRaw = $googleProfile['email_verified'] ?? false;
$emailVerified = $emailVerifiedRaw === true
    || $emailVerifiedRaw === 1
    || $emailVerifiedRaw === '1'
    || $emailVerifiedRaw === 'true';

if ($email === '' || !validate_email($email)) {
    set_flash('error', 'No se pudo validar tu cuenta de Google.');
    redirect_to_app('index.php');
}

if (!$emailVerified) {
    set_flash('error', 'Tu cuenta de Google no tiene correo verificado.');
    redirect_to_app('index.php');
}

if ($displayName === '') {
    $displayName = strstr($email, '@', true) ?: 'Usuario Google';
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, password, rol_id, proveedor_oauth)
         VALUES (?, ?, ?, 2, ?)'
    );
    $stmt->execute([
        $displayName,
        $email,
        password_hash(bin2hex(random_bytes(12)), PASSWORD_BCRYPT),
        'google',
    ]);

    $userId = (int)$pdo->lastInsertId();
} else {
    $userId = (int)$user['id'];

    if ((int)$user['bloqueado'] === 1) {
        set_flash('warning', 'Tu cuenta está bloqueada. Contacta al administrador.');
        redirect_to_app('index.php');
    }
}

$userWithRole = get_user_with_role($pdo, $userId);

if (!$userWithRole) {
    set_flash('error', 'No se pudo cargar la información del usuario.');
    redirect_to_app('index.php');
}

start_user_session($pdo, $userWithRole);

$stmt = $pdo->prepare(
    'UPDATE usuarios
     SET intentos_fallidos = 0, bloqueado = 0, ultimo_login = NOW(), proveedor_oauth = ?
     WHERE id = ?'
);
$stmt->execute(['google', $userId]);

register_log($pdo, $userId, 'LOGIN_OAUTH', 'Inicio de sesión mediante Google');
set_flash('success', 'Inicio de sesión con Google realizado correctamente.');
redirect_to_app('panel.php');
