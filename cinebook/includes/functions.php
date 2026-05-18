<?php
require_once __DIR__ . '/config.php';

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin()    { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function requireLogin() {
    if (!isLoggedIn()) {
        flash('Please log in to continue.', 'danger');
        header('Location: ' . SITE_URL . '/login.php'); exit;
    }
}
function requireAdmin() {
    if (!isAdmin()) {
        flash('Access denied.', 'danger');
        header('Location: ' . SITE_URL . '/login.php'); exit;
    }
}

// CSRF helpers
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}
function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('Invalid request token. Please go back and try again.');
    }
}

function flash($msg = null, $type = 'success') {
    if ($msg === null) {
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash']; unset($_SESSION['flash']);
            $icon = $f['type'] === 'success' ? '✓' : '✕';
            return '<div class="alert alert-' . e($f['type']) . '">' . $icon . ' ' . e($f['msg']) . '</div>';
        }
        return '';
    }
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function bookedSeats($conn, $show_id) {
    $stmt = $conn->prepare("SELECT seats FROM bookings WHERE show_id=? AND status='confirmed'");
    $stmt->bind_param('i', $show_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $taken = [];
    while ($row = $res->fetch_assoc()) {
        foreach (explode(',', $row['seats']) as $s) { $taken[] = trim($s); }
    }
    return $taken;
}

function generateBookingCode() {
    return 'BK' . strtoupper(bin2hex(random_bytes(4)));
}

// Format duration as "2h 28m"
function formatDuration($minutes) {
    if (!$minutes) return '—';
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return ($h ? "{$h}h " : '') . ($m ? "{$m}m" : '');
}
