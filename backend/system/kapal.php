<?php
require_once __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
// === ADD DATA ===
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("
        SELECT k.*, p.nama_pemilik, d.nama_dpi 
        FROM kapal k 
        LEFT JOIN pemilik p ON k.id_pemilik = p.id 
        LEFT JOIN dpi d ON k.id_dpi = d.id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    if (count($rows) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    } elseif (count($rows) === 1) {
        // 🔹 Hanya satu kapal → kirim objek tunggal
        echo json_encode([
            'success' => true,
            'data' => $rows[0]
        ]);
    } else {
        // 🔹 Banyak kapal → kirim array
        echo json_encode([
            'success' => true,
            'data' => $rows
        ]);
    }

    $stmt->close();
}
// === UPDATE STATUS ===
elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    $status = intval($data['status'] ?? 0);

    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'ID kapal tidak valid']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE kapal SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status kapal berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $conn->error]);
    }
    $stmt->close();
} else if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request
    $nama_kapal   = trim($data['nama_kapal'] ?? '');
    $jenis_kapal  = trim($data['jenis_kapal'] ?? '');
    $id_pemilik   = intval($data['id_pemilik'] ?? 0);
    $id_dpi       = intval($data['id_dpi'] ?? 0);
    $verification = intval($data['verification'] ?? 0);
    $status       = intval($data['status'] ?? 0);

    // Tentukan verified_at berdasarkan status verifikasi
    if ($verification !== 0 && $verification !== 1) {
        echo json_encode(['success' => false, 'message' => 'Status verifikasi tidak valid']);
        exit;
    }

    $verified_at = ($verification === 1) ? date('Y-m-d H:i:s') : null;

    // Validasi field wajib
    if ($nama_kapal === '' || $jenis_kapal === '' || $id_pemilik === 0 || $id_dpi === 0) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // Buat query INSERT
    $stmt = $conn->prepare("
        INSERT INTO kapal (nama_kapal, jenis_kapal, id_pemilik, id_dpi, verified_at, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Gunakan "s" untuk string, "i" untuk integer
    $stmt->bind_param(
        "ssiisi",
        $nama_kapal,
        $jenis_kapal,
        $id_pemilik,
        $id_dpi,
        $verified_at,
        $status
    );

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data kapal berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data: ' . $stmt->error]);
    }

    $stmt->close();
}

// === EDIT DATA ===
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = intval($data['id'] ?? 0);
    $nama_kapal    = trim($data['nama_kapal'] ?? '');
    $jenis_kapal   = trim($data['jenis_kapal'] ?? '');
    $id_pemilik    = intval($data['id_pemilik'] ?? 0);
    $id_dpi        = intval($data['id_dpi'] ?? 0);
    $verification  = intval($data['verification'] ?? 0);
    $status        = intval($data['status'] ?? 0);

    // ✅ Validasi field wajib
    if ($id === 0 || $nama_kapal === '' || $jenis_kapal === '' || $id_pemilik === 0 || $id_dpi === 0) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    // ✅ Validasi nilai verifikasi hanya boleh 0 atau 1
    if ($verification !== 0 && $verification !== 1) {
        echo json_encode(['success' => false, 'message' => 'Status verifikasi tidak valid']);
        exit;
    }

    // ✅ Tentukan verified_at sesuai status verifikasi
    $verified_at = ($verification === 1) ? date('Y-m-d H:i:s') : null;

    // ✅ Update data kapal
    $stmt = $conn->prepare("
        UPDATE kapal 
        SET nama_kapal = ?, 
            jenis_kapal = ?, 
            id_pemilik = ?, 
            id_dpi = ?, 
            verified_at = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssiisii",
        $nama_kapal,
        $jenis_kapal,
        $id_pemilik,
        $id_dpi,
        $verified_at,
        $status,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data kapal berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . $stmt->error]);
    }

    $stmt->close();
}

// === DELETE DATA ===
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id']);
    $stmt = $conn->prepare("DELETE FROM kapal WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data kapal berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $conn->error]);
    }
    $stmt->close();
}

// === FETCH DATA (default) ===
else {
    $query = "SELECT k.*, p.nama_pemilik, d.nama_dpi 
              FROM kapal k 
              LEFT JOIN pemilik p ON k.id_pemilik = p.id 
              LEFT JOIN dpi d ON k.id_dpi = d.id 
              ORDER BY k.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $kapalData = [];
    while ($row = $result->fetch_assoc()) {
        $kapalData[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $kapalData]);
    $stmt->close();
}

// Tutup koneksi
$conn->close();
