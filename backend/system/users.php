<?php
require_once __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// === ADD DATA ===
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($data['nama'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($nama === '' || $email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // Cek apakah email sudah terdaftar
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan user: ' . $conn->error]);
    }
    $stmt->close();
}

// === EDIT DATA ===
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    $nama = trim($data['nama'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? null;

    if ($id === 0 || $nama === '' || $email === '') {
        echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi']);
        exit;
    }

    // Cek apakah email sudah terdaftar oleh user lain
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar oleh user lain']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    if ($password) {
        // Update dengan password baru
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $email, $hashedPassword, $id);
    } else {
        // Update tanpa mengubah password
        $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $email, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui user: ' . $conn->error]);
    }
    $stmt->close();
}

// === DELETE DATA ===
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id']);
    
    // Cegah penghapusan user utama (id = 1)
    if ($id === 1) {
        echo json_encode(['success' => false, 'message' => 'User utama tidak dapat dihapus']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus user: ' . $conn->error]);
    }
    $stmt->close();
}

// === FETCH DATA (default) ===
else {
    $query = "SELECT id, nama, email, created_at FROM users ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $usersData = [];
    while ($row = $result->fetch_assoc()) {
        $usersData[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $usersData]);
    $stmt->close();
}

// Tutup koneksi
$conn->close();
?>