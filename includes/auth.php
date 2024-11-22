<?php
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function regenerateSession() {
    session_regenerate_id(true);
}

function loginUser($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_type'] = $user_data['type'] ?? 'user';
    $_SESSION['last_activity'] = time();
    regenerateSession();
}

function checkSessionTimeout() {
    $timeout = 30 * 60; // 30 minutos
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        return true;
    }
    $_SESSION['last_activity'] = time();
    return false;
} 