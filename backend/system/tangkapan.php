<?php
require_once __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// === GET DATA BY PEMILIK ===
if (isset($_GET['id_pemilik'])) {
    $id_pemilik = intval($_GET['id_pemilik']);
    $stmt = $conn->prepare("
        SELECT t.*, k.nama_kapal, d.nama_dpi, p.nama_pemilik 
        FROM tangkapan t 
        LEFT JOIN kapal k ON t.id_kapal = k.id 
        LEFT JOIN dpi d ON t.id_dpi = d.id 
        LEFT JOIN pemilik p ON t.id_pemilik = p.id 
        WHERE t.id_pemilik = ?
        ORDER BY t.tanggal_tangkapan DESC, t.waktu_tangkapan DESC
    ");
    $stmt->bind_param("i", $id_pemilik);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    if (count($rows) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tangkapan tidak ditemukan'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => $rows
        ]);
    }

    $stmt->close();
}

// === GET DATA BY ID ===
elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("
        SELECT t.*, k.nama_kapal, d.nama_dpi, p.nama_pemilik 
        FROM tangkapan t 
        LEFT JOIN kapal k ON t.id_kapal = k.id 
        LEFT JOIN dpi d ON t.id_dpi = d.id 
        LEFT JOIN pemilik p ON t.id_pemilik = p.id 
        WHERE t.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tangkapan tidak ditemukan'
        ]);
    } else {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    }

    $stmt->close();
}

// === ADD DATA TANGKAPAN ===
elseif ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request
    $id_kapal          = intval($data['id_kapal'] ?? 0);
    $id_pemilik        = intval($data['id_pemilik'] ?? 0);
    $nama_ikan         = trim($data['nama_ikan'] ?? '');
    $berat_ikan        = floatval($data['berat_ikan'] ?? 0);
    $harga_perkilo     = floatval($data['harga_perkilo'] ?? 0);
    $id_dpi            = intval($data['id_dpi'] ?? 0);
    $tanggal_tangkapan = trim($data['tanggal_tangkapan'] ?? '');
    $waktu_tangkapan   = trim($data['waktu_tangkapan'] ?? '');

    // Hitung total otomatis
    $total = $berat_ikan * $harga_perkilo;

    // Validasi field wajib
    if ($id_kapal === 0 || $id_pemilik === 0 || $nama_ikan === '' || $berat_ikan <= 0 || 
        $harga_perkilo <= 0 || $id_dpi === 0 || $tanggal_tangkapan === '' || $waktu_tangkapan === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi dengan benar']);
        exit;
    }

    // Validasi format tanggal
    if (!DateTime::createFromFormat('Y-m-d', $tanggal_tangkapan)) {
        echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid (harus YYYY-MM-DD)']);
        exit;
    }

    // Validasi format waktu
    if (!DateTime::createFromFormat('H:i', $waktu_tangkapan)) {
        echo json_encode(['success' => false, 'message' => 'Format waktu tidak valid (harus HH:MM)']);
        exit;
    }

    // Cek apakah kapal milik pemilik yang sama
    $check_stmt = $conn->prepare("SELECT id FROM kapal WHERE id = ? AND id_pemilik = ?");
    $check_stmt->bind_param("ii", $id_kapal, $id_pemilik);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Kapal tidak ditemukan atau bukan milik Anda']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Buat query INSERT
    $stmt = $conn->prepare("
        INSERT INTO tangkapan 
        (id_kapal, id_pemilik, nama_ikan, berat_ikan, harga_perkilo, total, id_dpi, tanggal_tangkapan, waktu_tangkapan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisdddiss",
        $id_kapal,
        $id_pemilik,
        $nama_ikan,
        $berat_ikan,
        $harga_perkilo,
        $total,
        $id_dpi,
        $tanggal_tangkapan,
        $waktu_tangkapan
    );

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Data tangkapan berhasil ditambahkan',
            'id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data: ' . $stmt->error]);
    }

    $stmt->close();
}

// === EDIT DATA TANGKAPAN ===
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id                = intval($data['id'] ?? 0);
    $id_kapal          = intval($data['id_kapal'] ?? 0);
    $id_pemilik        = intval($data['id_pemilik'] ?? 0);
    $nama_ikan         = trim($data['nama_ikan'] ?? '');
    $berat_ikan        = floatval($data['berat_ikan'] ?? 0);
    $harga_perkilo     = floatval($data['harga_perkilo'] ?? 0);
    $id_dpi            = intval($data['id_dpi'] ?? 0);
    $tanggal_tangkapan = trim($data['tanggal_tangkapan'] ?? '');
    $waktu_tangkapan   = trim($data['waktu_tangkapan'] ?? '');

    // Hitung total otomatis
    $total = $berat_ikan * $harga_perkilo;

    // Validasi field wajib
    if ($id === 0 || $id_kapal === 0 || $id_pemilik === 0 || $nama_ikan === '' || 
        $berat_ikan <= 0 || $harga_perkilo <= 0 || $id_dpi === 0 || 
        $tanggal_tangkapan === '' || $waktu_tangkapan === '') {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi dengan benar']);
        exit;
    }

    // Validasi format tanggal
    if (!DateTime::createFromFormat('Y-m-d', $tanggal_tangkapan)) {
        echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid (harus YYYY-MM-DD)']);
        exit;
    }

   

    // Cek apakah data tangkapan milik pemilik yang sama
    $check_stmt = $conn->prepare("SELECT id FROM tangkapan WHERE id = ? AND id_pemilik = ?");
    $check_stmt->bind_param("ii", $id, $id_pemilik);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Data tangkapan tidak ditemukan atau bukan milik Anda']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Cek apakah kapal milik pemilik yang sama
    $check_kapal_stmt = $conn->prepare("SELECT id FROM kapal WHERE id = ? AND id_pemilik = ?");
    $check_kapal_stmt->bind_param("ii", $id_kapal, $id_pemilik);
    $check_kapal_stmt->execute();
    $check_kapal_result = $check_kapal_stmt->get_result();

    if ($check_kapal_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Kapal tidak ditemukan atau bukan milik Anda']);
        $check_kapal_stmt->close();
        exit;
    }
    $check_kapal_stmt->close();

    // Update data tangkapan
    $stmt = $conn->prepare("
        UPDATE tangkapan 
        SET id_kapal = ?, 
            nama_ikan = ?, 
            berat_ikan = ?, 
            harga_perkilo = ?, 
            total = ?,
            id_dpi = ?,
            tanggal_tangkapan = ?,
            waktu_tangkapan = ?
        WHERE id = ? AND id_pemilik = ?
    ");

    $stmt->bind_param(
        "isdddissii",
        $id_kapal,
        $nama_ikan,
        $berat_ikan,
        $harga_perkilo,
        $total,
        $id_dpi,
        $tanggal_tangkapan,
        $waktu_tangkapan,
        $id,
        $id_pemilik
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data tangkapan berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . $stmt->error]);
    }

    $stmt->close();
}

// === DELETE DATA TANGKAPAN ===
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($data['id'] ?? 0);
    $id_pemilik = intval($data['id_pemilik'] ?? 0);

    // Validasi
    if ($id === 0 || $id_pemilik === 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }

    // Cek apakah data milik pemilik yang sama
    $check_stmt = $conn->prepare("SELECT id FROM tangkapan WHERE id = ? AND id_pemilik = ?");
    $check_stmt->bind_param("ii", $id, $id_pemilik);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Data tangkapan tidak ditemukan atau bukan milik Anda']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Hapus data
    $stmt = $conn->prepare("DELETE FROM tangkapan WHERE id = ? AND id_pemilik = ?");
    $stmt->bind_param("ii", $id, $id_pemilik);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data tangkapan berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $conn->error]);
    }
    $stmt->close();
}

// === GET ALL DATA (default) ===
else {
    $query = "
        SELECT t.*, k.nama_kapal, d.nama_dpi, p.nama_pemilik 
        FROM tangkapan t 
        LEFT JOIN kapal k ON t.id_kapal = k.id 
        LEFT JOIN dpi d ON t.id_dpi = d.id 
        LEFT JOIN pemilik p ON t.id_pemilik = p.id 
        ORDER BY t.tanggal_tangkapan DESC, t.waktu_tangkapan DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $tangkapanData = [];
    while ($row = $result->fetch_assoc()) {
        $tangkapanData[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $tangkapanData]);
    $stmt->close();
}

// Tutup koneksi
$conn->close();
?>