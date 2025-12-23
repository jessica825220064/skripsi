<?php
header('Content-Type: application/json');

require_once 'koneksi.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($conn->connect_error) {
    echo json_encode([
        "error" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit;
}

$jenisData = isset($_GET['jenis']) ? $_GET['jenis'] : 'kecelakaan';

switch ($jenisData) {
    case 'karyawan':
        $tableName = 'data_karyawan';
        break;
    case 'absensi':
        $tableName = 'data_absensi';
        break;
    case 'shift':
        $tableName = 'data_shift';
        break;
    case 'produksi':
        $tableName = 'data_produksi';
        break;
    case 'kecelakaan':
    default:
        $tableName = 'data_kecelakaan_kerja';
}

$sql = "SELECT * FROM " . $tableName;
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

echo json_encode($data);
?>
