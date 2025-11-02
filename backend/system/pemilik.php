<?php
require_once __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Cegah error tidak relevan
if (isset($_GET['id'])) {
    $id = $_GET["id"];
    $stmt = $conn->prepare("SELECT * FROM pemilik WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pemilik = $result->fetch_assoc();
    if (!$pemilik) {
        echo json_encode(['success' => false, 'message' => 'ID Pemilik tidak ditemukan']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => $pemilik]);
    exit;
}

// === ADD DATA ===
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemilik = trim($data['nama_pemilik'] ?? '');
    $email = trim($data['email'] ?? '');
    $alamat = trim($data['alamat'] ?? '');
    $nomor_telepon = trim($data['nomor_telepon'] ?? '');
    $nik = trim($data['nik'] ?? '');
    $password = trim($data['password'] ?? 'password123');

    // Validasi input
    if ($nama_pemilik === '' || $email === '' || $alamat === '' || $nomor_telepon === '' || $nik === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }

    // Cek apakah email sudah terdaftar
    $checkStmt = $conn->prepare("SELECT id FROM pemilik WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Cek apakah NIK sudah terdaftar
    $checkStmt = $conn->prepare("SELECT id FROM pemilik WHERE nik = ?");
    $checkStmt->bind_param("s", $nik);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'NIK sudah terdaftar']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $current_time = date('Y-m-d H:i:s');

    // Insert data dengan semua field
    $stmt = $conn->prepare("INSERT INTO pemilik (nama_pemilik, email, alamat, nomor_telepon, nik, password, last_login, created_at, whatsapp_verified, whatsapp_verified_at) VALUES (?, ?, ?, ?, ?, ?, NULL, ?, 0, NULL)");
    $stmt->bind_param("sssssss", $nama_pemilik, $email, $alamat, $nomor_telepon, $nik, $hashedPassword, $current_time);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data Pemilik berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data: ' . $conn->error]);
    }
    $stmt->close();
}

// === EDIT DATA ===
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    $nama_pemilik = trim($data['nama_pemilik'] ?? '');
    $email = trim($data['email'] ?? '');
    $alamat = trim($data['alamat'] ?? '');
    $nomor_telepon = trim($data['nomor_telepon'] ?? '');
    $nik = trim($data['nik'] ?? '');

    // Validasi input
    if ($id === 0 || $nama_pemilik === '' || $email === '' || $alamat === '' || $nomor_telepon === '' || $nik === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }

    // Cek apakah email sudah digunakan oleh user lain
    $checkStmt = $conn->prepare("SELECT id FROM pemilik WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh user lain']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Cek apakah NIK sudah digunakan oleh user lain
    $checkStmt = $conn->prepare("SELECT id FROM pemilik WHERE nik = ? AND id != ?");
    $checkStmt->bind_param("si", $nik, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'NIK sudah digunakan oleh user lain']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Update data (hanya field yang boleh diubah melalui admin)
    $stmt = $conn->prepare("UPDATE pemilik SET nama_pemilik = ?, email = ?, alamat = ?, nomor_telepon = ?, nik = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nama_pemilik, $email, $alamat, $nomor_telepon, $nik, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data Pemilik berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . $conn->error]);
    }
    $stmt->close();
}

// === RESET PASSWORD ===
elseif ($action === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    
    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'ID Pemilik tidak valid']);
        exit;
    }

    // Password default
    $defaultPassword = 'password123';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE pemilik SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password berhasil direset ke default: ' . $defaultPassword]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal reset password: ' . $conn->error]);
    }
    $stmt->close();
}

// === DELETE DATA ===
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id']);
    
    // Cek apakah pemilik memiliki kapal
    $checkStmt = $conn->prepare("SELECT COUNT(*) as total_kapal FROM kapal WHERE id_pemilik = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $kapalCount = $checkResult->fetch_assoc()['total_kapal'];
    $checkStmt->close();

    if ($kapalCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus pemilik karena masih memiliki ' . $kapalCount . ' kapal terdaftar']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM pemilik WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data Pemilik berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $conn->error]);
    }
    $stmt->close();
}

// === EDIT PROFILE (untuk pemilik mengedit profil sendiri) ===
elseif ($action === 'edit_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    $nama_pemilik = trim($data['nama_pemilik'] ?? '');
    $email = trim($data['email'] ?? '');
    $alamat = trim($data['alamat'] ?? '');
    $nomor_telepon = trim($data['nomor_telepon'] ?? '');
    $password = $data['password'] ?? '';

    // Validasi input
    if ($id === 0 || $nama_pemilik === '' || $email === '' || $alamat === '' || $nomor_telepon === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }

    // Cek apakah email sudah digunakan oleh user lain
    $checkStmt = $conn->prepare("SELECT id FROM pemilik WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh user lain']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Update data dengan atau tanpa password
    if ($password !== '') {
        // Jika password diubah
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE pemilik SET nama_pemilik = ?, email = ?, alamat = ?, nomor_telepon = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nama_pemilik, $email, $alamat, $nomor_telepon, $hashedPassword, $id);
    } else {
        // Jika password tidak diubah
        $stmt = $conn->prepare("UPDATE pemilik SET nama_pemilik = ?, email = ?, alamat = ?, nomor_telepon = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama_pemilik, $email, $alamat, $nomor_telepon, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil: ' . $conn->error]);
    }
    $stmt->close();
}

// === FETCH DATA (default) ===
else {
    $query = "SELECT id, nama_pemilik, email, alamat, nomor_telepon, nik, last_login, created_at, whatsapp_verified, whatsapp_verified_at FROM pemilik ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $pemilikData = [];
    while ($row = $result->fetch_assoc()) {
        $pemilikData[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $pemilikData]);
    $stmt->close();
}

// Tutup koneksi di paling akhir
$conn->close();
?>