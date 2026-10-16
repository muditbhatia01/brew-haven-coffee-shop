<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}
require_once __DIR__ . '/../config/db.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function is_logged_in(): bool {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: ../login.php');
        exit;
    }
}
function cart_count(): int {
    $count = 0;
    foreach (($_SESSION['cart'] ?? []) as $qty) $count += (int)$qty;
    return $count;
}
function money(float $amount): string {
    return 'Rp' . number_format($amount, 0, ',', '.');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }
}
