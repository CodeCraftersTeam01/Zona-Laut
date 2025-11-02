<?php 

require_once 'koneksi.php';

$sqlDPI = "SELECT COUNT(*) AS jumlah_dpi FROM dpi"; 
$resultDPI = $conn->query($sqlDPI);
$rowDPI = $resultDPI->fetch_assoc();
$jumlahDPI = $rowDPI['jumlah_dpi'];

$sqlKAPAL = "SELECT COUNT(*) AS jumlah_kapal FROM kapal";
$resultKAPAL = $conn->query($sqlKAPAL);
$rowKAPAL = $resultKAPAL->fetch_assoc();
$jumlahKAPAL = $rowKAPAL['jumlah_kapal'];

$sqlPEMILIK = "SELECT COUNT(*) AS jumlah_pemilik FROM pemilik";
$resultPEMILIK = $conn->query($sqlPEMILIK);
$rowPEMILIK = $resultPEMILIK->fetch_assoc();
$jumlahPEMILIK = $rowPEMILIK['jumlah_pemilik'];

$sqlUSER = "SELECT COUNT(*) AS jumlah_user FROM users";
$resultUSER = $conn->query($sqlUSER);
$rowUSER = $resultUSER->fetch_assoc();
$jumlahUSER = $rowUSER['jumlah_user'];

echo json_encode([
    'success' => true,
    'jumlah_dpi' => $jumlahDPI,
    'jumlah_kapal' => $jumlahKAPAL,
    'jumlah_pemilik' => $jumlahPEMILIK,
    'jumlah_user' => $jumlahUSER
]);