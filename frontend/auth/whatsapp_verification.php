<?php
require_once '../../backend/system/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

class WhatsAppVerification {
    private $conn;
    private $fonteeApiKey;
    private $fonteeBaseUrl = 'https://api.fonnte.com';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->fonteeApiKey = 'eNF5Ugn2HWGBcrbDUyx2';
    }

    private function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function saveOTP($phone, $otp) {
        try {
            $formattedPhone = $this->formatPhoneNumber($phone);
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            error_log("Attempting to save OTP for phone: $formattedPhone, OTP: $otp");
            
            // First check if table exists, if not create it
            $this->createTableIfNotExists();
            
            $stmt = $this->conn->prepare(
                "INSERT INTO whatsapp_verifications (phone_number, otp_code, expires_at, created_at) 
                 VALUES (?, ?, ?, NOW()) 
                 ON DUPLICATE KEY UPDATE 
                 otp_code = ?, expires_at = ?, created_at = NOW(), used_at = NULL"
            );
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssss", $formattedPhone, $otp, $expires_at, $otp, $expires_at);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            error_log("OTP saved successfully for phone: $formattedPhone");
            return true;
            
        } catch (Exception $e) {
            error_log("Save OTP Error: " . $e->getMessage());
            return false;
        }
    }

    private function createTableIfNotExists() {
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS whatsapp_verifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            phone_number VARCHAR(20) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            UNIQUE KEY unique_phone (phone_number)
        )";
        
        if (!$this->conn->query($createTableSQL)) {
            error_log("Create table failed: " . $this->conn->error);
        }
    }

    public function sendOTP($phone) {
        try {
            $otp = $this->generateOTP();
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            error_log("Sending OTP process started for: $formattedPhone");
            
            // Save to database
            if (!$this->saveOTP($phone, $otp)) {
                throw new Exception('Gagal menyimpan OTP ke database');
            }

            // Verify the OTP was saved
            $verifyStmt = $this->conn->prepare(
                "SELECT otp_code FROM whatsapp_verifications WHERE phone_number = ?"
            );
            $verifyStmt->bind_param("s", $formattedPhone);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyResult->num_rows === 0) {
                throw new Exception('OTP tidak tersimpan di database setelah proses penyimpanan');
            }
            
            $savedOTP = $verifyResult->fetch_assoc()['otp_code'];
            error_log("Verified OTP saved in DB: $savedOTP for phone: $formattedPhone");
            
            // Prepare message
            $message = "Kode verifikasi Zona Laut Enterprise: *{$otp}*\n\nKode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapapun.";

            // Try sending via Fonnte
            $fonnteResult = $this->sendViaFonnte($formattedPhone, $message);
            
            $response = [
                'success' => true,
                'message' => 'OTP berhasil dibuat',
                'debug' => [
                    'phone' => $formattedPhone,
                    'otp' => $otp,
                    'fonnte_result' => $fonnteResult
                ]
            ];

            if ($fonnteResult['success']) {
                $response['message'] = 'OTP berhasil dikirim ke WhatsApp Anda';
            } else {
                $response['message'] = 'OTP berhasil dibuat. Jika tidak menerima WhatsApp, gunakan kode: ' . $otp;
            }

            return $response;

        } catch (Exception $e) {
            error_log("Send OTP Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP: ' . $e->getMessage(),
                'debug' => ['error' => $e->getMessage()]
            ];
        }
    }

    private function formatPhoneNumber($phone) {
        // Remove all non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);
        
        // Handle different formats
        if (substr($cleaned, 0, 1) === '0') {
            // 08xxx -> 628xxx
            return '62' . substr($cleaned, 1);
        } elseif (substr($cleaned, 0, 2) === '62') {
            // 62xxx -> tetap
            return $cleaned;
        } elseif (substr($cleaned, 0, 1) === '8') {
            // 8xxx -> 628xxx
            return '62' . $cleaned;
        } else {
            // Return as is
            return $cleaned;
        }
    }

    private function sendViaFonnte($phone, $message) {
        $url = $this->fonteeBaseUrl . '/send';
        
        $postData = [
            'target' => $phone,
            'message' => $message,
        ];

        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->fonteeApiKey
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log("Fonnte API - Phone: $phone, HTTP: $httpCode, Error: $curlError");

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $curlError
            ];
        }

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['status']) && $result['status'] === true) {
                return [
                    'success' => true,
                    'message' => 'Pesan terkirim'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Unknown Fonnte error'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "HTTP Error: $httpCode"
            ];
        }
    }

    public function verifyOTP($phone, $code) {
        try {
            // Format phone number untuk konsistensi
            $formattedPhone = $this->formatPhoneNumber($phone);

            error_log("Verifying OTP for phone: $formattedPhone, code: $code");

            $stmt = $this->conn->prepare(
                "SELECT id, expires_at, otp_code, phone_number
                 FROM whatsapp_verifications 
                 WHERE phone_number = ? AND used_at IS NULL
                 ORDER BY created_at DESC 
                 LIMIT 1"
            );
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $formattedPhone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            error_log("Database query result rows: " . $result->num_rows);
            
            if ($result->num_rows === 0) {
                // Coba lihat apa yang ada di database untuk nomor ini
                $debugStmt = $this->conn->prepare(
                    "SELECT id, phone_number, otp_code, expires_at, used_at, created_at 
                     FROM whatsapp_verifications 
                     WHERE phone_number = ? 
                     ORDER BY created_at DESC 
                     LIMIT 5"
                );
                $debugStmt->bind_param("s", $formattedPhone);
                $debugStmt->execute();
                $debugResult = $debugStmt->get_result();
                
                $debugData = [];
                while ($row = $debugResult->fetch_assoc()) {
                    $debugData[] = $row;
                }
                
                error_log("Debug data for phone $formattedPhone: " . json_encode($debugData));
                
                return [
                    'success' => false,
                    'message' => 'Tidak ada kode OTP yang aktif untuk nomor ini',
                    'debug' => [
                        'phone' => $formattedPhone,
                        'database_entries' => $debugData
                    ]
                ];
            }
            
            $verification = $result->fetch_assoc();
            error_log("Found OTP record: " . json_encode($verification));
            
            // Check if OTP matches
            if ($verification['otp_code'] !== $code) {
                return [
                    'success' => false,
                    'message' => 'Kode OTP tidak valid. Harap periksa kembali.',
                    'debug' => [
                        'expected' => $verification['otp_code'],
                        'provided' => $code
                    ]
                ];
            }
            
            // Check if OTP is expired
            if (strtotime($verification['expires_at']) < time()) {
                return [
                    'success' => false,
                    'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.',
                    'debug' => [
                        'expires_at' => $verification['expires_at'],
                        'current_time' => date('Y-m-d H:i:s')
                    ]
                ];
            }
            
            // Mark as used
            $updateStmt = $this->conn->prepare(
                "UPDATE whatsapp_verifications SET used_at = NOW() WHERE id = ?"
            );
            $updateStmt->bind_param("i", $verification['id']);
            $updateStmt->execute();
            
            // Update user verification status
            $userStmt = $this->conn->prepare(
                "UPDATE pemilik SET whatsapp_verified = 1, whatsapp_verified_at = NOW() 
                 WHERE nomor_telepon LIKE ? OR nomor_telepon LIKE ? OR nomor_telepon LIKE ?"
            );
            
            $phoneVariation1 = $formattedPhone; // 628xxx
            $phoneVariation2 = '0' . substr($formattedPhone, 2); // 08xxx
            $phoneVariation3 = substr($formattedPhone, 2); // 8xxx
            
            $userStmt->bind_param("sss", $phoneVariation1, $phoneVariation2, $phoneVariation3);
            $userStmt->execute();
            
            error_log("OTP verification successful for phone: $formattedPhone");
            
            return [
                'success' => true,
                'message' => 'WhatsApp berhasil diverifikasi!'
            ];
            
        } catch (Exception $e) {
            error_log("Verify OTP Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }
}

// Handle requests
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

error_log("WhatsApp Verification Request: " . json_encode($data));

try {
    $whatsappVerification = new WhatsAppVerification($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'send_otp':
                $phone = $data['phone'] ?? '';
                
                if (empty($phone)) {
                    echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tidak boleh kosong']);
                    exit;
                }
                
                $result = $whatsappVerification->sendOTP($phone);
                echo json_encode($result);
                break;
                
            case 'verify_otp':
                $phone = $data['phone'] ?? '';
                $code = $data['code'] ?? '';
                
                if (empty($phone) || empty($code)) {
                    echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp dan kode tidak boleh kosong']);
                    exit;
                }
                
                $result = $whatsappVerification->verifyOTP($phone, $code);
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Action tidak valid: ' . $action]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan POST.']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

$conn->close();
?>