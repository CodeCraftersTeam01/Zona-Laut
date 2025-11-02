<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'success' => true,
        'user_id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'login_time' => $_SESSION['login_time']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
}
?>