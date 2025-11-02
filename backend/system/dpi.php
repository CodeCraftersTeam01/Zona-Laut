<?php
require_once __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Cegah error tidak relevan
if (isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID DPI tidak ditemukan']);
    exit;
}

// === ADD DATA ===
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($data['nama_dpi'] ?? '');
    $luas = trim($data['luas'] ?? '');
    $location = trim($data['location'] ?? '');

    if ($nama === '' || $luas === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO dpi (nama_dpi, luas, location) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nama, $luas, $location);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data DPI berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data: ' . $conn->error]);
    }
    $stmt->close();
}

// === EDIT DATA ===
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($data['id'] ?? '');
    $nama = trim($data['nama_dpi'] ?? '');
    $luas = trim($data['luas'] ?? '');
    $location = trim($data['location'] ?? '');

    if ($id === '' || $nama === '' || $luas === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE dpi SET nama_dpi = ?, luas = ?, location = ? WHERE id = ?");
    $stmt->bind_param("sisi", $nama, $luas, $location, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data DPI berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . $conn->error]);
    }
    $stmt->close();
}

// === DELETE DATA ===
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id']);
    $stmt = $conn->prepare("DELETE FROM dpi WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data DPI berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $conn->error]);
    }
    $stmt->close();
}

// === FETCH DATA (default) ===
else {
    $query = "SELECT * FROM dpi";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $dpiData = [];
    while ($row = $result->fetch_assoc()) {
        $dpiData[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $dpiData]);
    $stmt->close();
}

// Tutup koneksi di paling akhir
$conn->close();
?>
