
<?php

// Configure session security before starting the session.
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    $isHttps = (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off'
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Create a CSRF token for form protection.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );
}

// Escape output to help prevent XSS.
function e($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Check whether a user is logged in.
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

// Restrict pages to logged-in users.
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Check the user's role.
function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

// Validate CSRF tokens for POST requests.
function require_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $token
        )
    ) {
        http_response_code(403);
        exit('Permintaan tidak sah.');
    }
}
