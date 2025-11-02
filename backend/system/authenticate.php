<?php
require_once __DIR__ . '/koneksi.php';

// Pastikan session_start() di paling atas
session_start();

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode([
        'success' => false,
        'message' => 'Email dan password wajib diisi'
    ]);
    exit;
}

$query = "SELECT id, email, password FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    // Regenerate session ID untuk keamanan
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login berhasil'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Email atau password salah'
    ]);
}

$stmt->close();
$conn->close();
?>