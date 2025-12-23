<?php
header('Content-Type: application/json');
require_once 'koneksi.php'; 
ini_set('display_errors', 1);
error_reporting(E_ALL);

function sendResponse($status, $message, $details = []) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'details' => $details
    ]);
    exit;
}

// --- 1. Validasi File ---
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    sendResponse('error', 'Tidak ada file yang diunggah atau terjadi kesalahan upload.');
}

$fileTmp  = $_FILES['csv_file']['tmp_name'];
$fileName = $_FILES['csv_file']['name'];
$fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExt !== 'csv') {
    sendResponse('error', 'Tipe file tidak valid. Hanya CSV yang diperbolehkan.');
}
// --- 2. Tentukan Tabel dari Jenis Data ---
$jenisData = $_POST['jenis_data'] ?? '';

$tableMap = [
    'karyawan' => [
        'table' => 'data_karyawan',
        'required_headers' => [
            'KaryawanPIN', 'Nama', 'Departemen', 'Posisi', 'TanggalMasuk', 'NoShift'
        ]
    ],
    'absensi' => [
        'table' => 'data_absensi',
        'required_headers' => [
            'NoAbsen', 'KaryawanPIN', 'Tanggal', 'JamMasuk', 'JamKeluar', 'NoShift'
        ]
    ],
    'shift' => [
        'table' => 'data_shift',
        'required_headers' => [
            'NoShift', 'JamMasuk', 'JamKeluar'
        ]
    ],
    'produksi' => [
        'table' => 'data_produksi',
        'required_headers' => [
            'NoPO', 'Tanggal', 'Type', 'Brand', 'Size', 'ProduksiTarget', 'TargetSatuan',
            'ProduksiAktual', 'AktualSatuan', 'OprPIN', 'NoShift', 'Ext', 'SPKNo', 'CoilNo',
            'Panjang', 'PassedQty', 'PassedSatuan', 'PassedWarna', 'RejectQty', 'RejectSatuan',
            'Reject Reason', 'CoilPIN'
        ]
    ],
    'kecelakaan' => [
        'table' => 'data_kecelakaan_kerja',
        'required_headers' => [
            'NoKecelakaan', 'Tanggal', 'JenisInsiden', 'KaryawanPIN', 'Shift', 'Penyebab', 'TindakanPerbaikan'
        ]
    ]
];


if (!array_key_exists($jenisData, $tableMap)) {
    sendResponse('error', 'Jenis data tidak valid.');
}

$tableName = $tableMap[$jenisData]['table'];
$requiredHeaders = $tableMap[$jenisData]['required_headers'];

// --- 3. Baca CSV dan Validasi Header ---
if (($handle = fopen($fileTmp, "r")) === false) {
    sendResponse('error', 'Gagal membuka file CSV.');
}

$header = fgetcsv($handle, 1000, ",");

// File kosong
if ($header === false) {
    fclose($handle);
    sendResponse('error', 'File CSV kosong.');
}

// Trim spasi pada header
$header = array_map('trim', $header);

// Cek header lengkap
$missingHeaders = array_diff($requiredHeaders, $header);
if (!empty($missingHeaders)) {
    fclose($handle);
    sendResponse('error', 'Header CSV tidak sesuai. Kolom hilang: ' . implode(', ', $missingHeaders));
}

// --- 4. Proses Baris CSV ---
$inserted = 0;
$failed = [];
$rowNum = 1; 

while (($row = fgetcsv($handle, 1000, ",")) !== false) {
    $rowNum++;
    if (count($row) != count($header)) {
        $failed[] = ['row' => $rowNum, 'reason' => 'Jumlah kolom tidak sesuai header'];
        continue;
    }

    $data = array_combine($header, $row);

    // --- 5. Validasi Tipe Data ---
    $valid = true;
    $reason = '';
    foreach ($requiredHeaders as $col) {
        if (empty($data[$col])) {
            $valid = false;
            $reason = "Kolom '$col' kosong";
            break;
        }
    }
    if (!$valid) {
        $failed[] = ['row' => $rowNum, 'reason' => $reason];
        continue;
    }

    // --- 6. Cek Duplikasi ---
    $whereClauses = [];
    if ($jenisData === 'absensi' || $jenisData === 'produksi' || $jenisData === 'kecelakaan') {
        $whereClauses[] = "karyawanPIN='" . $conn->real_escape_string($data['karyawanPIN']) . "'";
        $whereClauses[] = "tanggal='" . $conn->real_escape_string($data['tanggal']) . "'";
    } elseif ($jenisData === 'karyawan') {
        $whereClauses[] = "karyawanPIN='" . $conn->real_escape_string($data['karyawanPIN']) . "'";
    } elseif ($jenisData === 'shift') {
        $whereClauses[] = "NoShift='" . $conn->real_escape_string($data['NoShift']) . "'";
    }

    $checkSQL = "SELECT COUNT(*) as cnt FROM $tableName WHERE " . implode(' AND ', $whereClauses);
    $res = $conn->query($checkSQL);
    $count = $res->fetch_assoc()['cnt'] ?? 0;
    if ($count > 0) {
        $failed[] = ['row' => $rowNum, 'reason' => 'Duplikat data'];
        continue;
    }

    // --- 7. Insert Data ---
    $cols = array_keys($data);
    $vals = array_map(function($val) use ($conn) {
        return "'" . $conn->real_escape_string($val) . "'";
    }, array_values($data));

    $insertSQL = "INSERT INTO $tableName (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
    if ($conn->query($insertSQL)) {
        $inserted++;
    } else {
        $failed[] = ['row' => $rowNum, 'reason' => 'Gagal insert: ' . $conn->error];
    }
}

fclose($handle);
$conn->close();

// --- 8. Kirim Respons JSON ---
$message = "Proses selesai. $inserted baris berhasil dimasukkan.";
if (!empty($failed)) {
    $message .= " " . count($failed) . " baris gagal diimpor.";
}

sendResponse('success', $message, $failed);

// --- 9. Trigger ETL Pentaho Otomatis ---
set_time_limit(0); 

$kitchenPath = "C:\Users\JW\Downloads\pdi-ce-9.4.0.0-343\data-integration\Kitchen.bat";
$jobPath     = "C:\Users\JW\pentaho\job_etl.kjb";

$command = "\"$kitchenPath\" /file:\"$jobPath\" /level:Basic 2>&1";

exec($command, $output, $return_var);

$etlStatus = ($return_var === 0) ? " ETL Berhasil" : " ETL Gagal ";
$message .= $etlStatus;
?>
