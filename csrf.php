<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate (or reuse) the session CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted token against the session token.
 * Returns true on success, false on failure.
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Call this in a POST handler: dies with 403 if token is bad.
 */
function require_csrf_token() {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted)) {
        http_response_code(403);
        die("CSRF Token Validation Failed. Please go back and try again.");
    }
}
?>
