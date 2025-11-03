<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../backend/system/koneksi.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Start session
session_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    if ($action == 'register') {
        handleRegister($conn, $input);
    } elseif ($action == 'login') {
        handleLogin($conn, $input);
    } elseif ($action == 'logout') {
        handleLogout();
    } elseif ($action == 'check_session') {
        checkSession();
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}

function handleRegister($conn, $data)
{
    // Validasi input
    if (empty($data['username']) || empty($data['email']) || empty($data['phone']) || empty($data['password']) || empty($data['address'])) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        return;
    }

    // Validasi email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        return;
    }

    // Validasi username
    if (!preg_match('/^[a-zA-Z0-9]{3,}$/', $data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Username harus minimal 3 karakter dan hanya mengandung huruf dan angka']);
        return;
    }

    // Validasi nomor telepon
    if (!preg_match('/^08[0-9]{9,11}$/', $data['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Format nomor telepon tidak valid']);
        return;
    }

    // Validasi password
    if (strlen($data['password']) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password harus minimal 8 karakter']);
        return;
    }

    // Escape data untuk keamanan
    $username = $conn->real_escape_string($data['username']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $address = $conn->real_escape_string($data['address']);

    // Cek apakah username atau email sudah ada
    $check = $conn->query("SELECT id FROM pemilik WHERE nama_pemilik = '$username' OR email = '$email'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username atau email sudah terdaftar']);
        return;
    }

    // Simpan data ke database
    $query = "INSERT INTO pemilik (nama_pemilik, email, nomor_telepon, password, alamat) 
              VALUES ('$username', '$email', '$phone', '$password', '$address')";

    if ($conn->query($query)) {
        // Dapatkan data user yang baru dibuat
        $user_id = $conn->insert_id;
        $user_query = $conn->query("SELECT id, nama_pemilik, email, nomor_telepon, alamat FROM pemilik WHERE id = '$user_id'");
        $user = $user_query->fetch_assoc();

        // Generate token
        $token = generateToken($user['id']);

        // Simpan session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nama_pemilik'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['token'] = $token;

        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan verifikasi WhatsApp Anda.',
            'user_id' => $user['id'],
            'user' => [
                'id' => $user['id'],
                'username' => $user['nama_pemilik'],
                'email' => $user['email'],
                'phone' => $user['nomor_telepon'],
                'address' => $user['alamat']
            ],
            'token' => $token,
            'needs_verification' => true, // Flag bahwa perlu verifikasi
            'redirect_url' => '../verify' // URL untuk redirect ke verifikasi
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error database: ' . $conn->error]);
    }
}

function handleLogin($conn, $data)
{
    // Validasi input
    if (empty($data['username']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
        return;
    }

    // Escape data untuk keamanan
    $username = $conn->real_escape_string($data['username']);

    // Cari user berdasarkan username atau email
    $query = "SELECT id, nama_pemilik, email, nomor_telepon, password, alamat, created_at, whatsapp_verified FROM pemilik 
              WHERE nama_pemilik = '$username' OR email = '$username'";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($data['password'], $user['password'])) {

            // PERBAIKAN: Update last login - gunakan prepared statement untuk keamanan
            $update_stmt = $conn->prepare("UPDATE pemilik SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            
            if ($update_stmt->execute()) {
                // Generate token
                $token = generateToken($user['id']);

                // Simpan session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nama_pemilik'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                $_SESSION['token'] = $token;

                // Prepare user data for response
                $userData = [
                    'id' => $user['id'],
                    'username' => $user['nama_pemilik'],
                    'email' => $user['email'],
                    'phone' => $user['nomor_telepon'],
                    'address' => $user['alamat'],
                    'created_at' => $user['created_at'],
                    'whatsapp_verified' => (bool)$user['whatsapp_verified']
                ];

                // Check if WhatsApp needs verification
                if ($user["whatsapp_verified"] == 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Harap verifikasi nomor WhatsApp terlebih dahulu',
                        'needs_verification' => true,
                        'user' => $userData,
                        'token' => $token,
                        'redirect_url' => '../verify' // URL untuk redirect
                    ]);
                    $update_stmt->close();
                    return;
                }

                // Jika sudah terverifikasi, lanjutkan login normal
                echo json_encode([
                    'success' => true,
                    'message' => 'Login berhasil!',
                    'user' => $userData,
                    'token' => $token
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal update last login: ' . $conn->error]);
            }
            
            $update_stmt->close();
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Password salah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username atau email tidak ditemukan']);
    }
}

function handleLogout()
{
    // Hapus semua data session
    session_unset();
    session_destroy();

    // Hapus cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
}

function checkSession()
{
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'message' => 'Tidak ada session aktif'
        ]);
    }
}

function generateToken($user_id)
{
    // Generate random token
    $token = bin2hex(random_bytes(32));

    // Simpan token di database (opsional, untuk remember me functionality)
    // $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    // $conn->query("INSERT INTO user_tokens (user_id, token, expires_at) VALUES ('$user_id', '$token', '$expires')");

    return $token;
}

// Fungsi untuk validasi token (opsional, untuk API protection)
function validateToken($conn, $token)
{
    // Cek token di database
    $query = $conn->query("SELECT user_id FROM user_tokens WHERE token = '$token' AND expires_at > NOW()");

    if ($query && $query->num_rows > 0) {
        $data = $query->fetch_assoc();
        return $data['user_id'];
    }

    return false;
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function getUserById($conn, $user_id)
{
    $query = $conn->query("SELECT id, nama_pemilik, email, nomor_telepon, alamat, created_at, last_login 
                          FROM pemilik WHERE id = '$user_id'");

    if ($query && $query->num_rows > 0) {
        return $query->fetch_assoc();
    }

    return null;
}

// Middleware untuk memeriksa authentication (opsional)
function requireAuth()
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

// Middleware untuk memeriksa admin (opsional)
function requireAdmin($conn)
{
    requireAuth();

    $user_id = $_SESSION['user_id'];
    $user = getUserById($conn, $user_id);

    // Tambahkan logika untuk memeriksa role admin di sini
    // Contoh: if ($user['role'] != 'admin') { ... }
}

// Tutup koneksi database
$conn->close();
