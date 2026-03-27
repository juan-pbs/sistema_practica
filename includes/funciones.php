<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $baseUrl = rtrim(BASE_URL, '/');
    $relativePath = ltrim($path, '/');

    return $relativePath === '' ? $baseUrl : $baseUrl . '/' . $relativePath;
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function redirect_to_app(string $path = ''): void
{
    redirect(app_url($path));
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function has_role(array $roles): bool
{
    return isset($_SESSION['rol']) && in_array($_SESSION['rol'], $roles, true);
}

function has_permission(string $permission): bool
{
    return isset($_SESSION['permisos']) && in_array($permission, $_SESSION['permisos'], true);
}

function post(string $key, string $default = ''): string
{
    return trim($_POST[$key] ?? $default);
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    if (!isset($_SESSION['csrf_token']) || $token === null || $token === '') {
        return false;
    }

    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function is_google_oauth_configured(): bool
{
    $placeholderClientId = 'TU_GOOGLE_CLIENT_ID';
    $placeholderClientSecret = 'TU_GOOGLE_CLIENT_SECRET';

    return GOOGLE_CLIENT_ID !== ''
        && GOOGLE_CLIENT_SECRET !== ''
        && GOOGLE_CLIENT_ID !== $placeholderClientId
        && GOOGLE_CLIENT_SECRET !== $placeholderClientSecret;
}
