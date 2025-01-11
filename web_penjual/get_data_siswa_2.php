<?php
include 'dbconn.php';
$conn = dbconn();

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_GET['uid_kartu'])) {
    $uid_kartu = $_GET['uid_kartu'];

    // Query untuk mendapatkan nama siswa berdasarkan UID
    $query = "SELECT saldo, nama FROM tabel_siswa WHERE uid_kartu = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $uid_kartu);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $siswa = $result->fetch_assoc();
        echo json_encode([
            "nama" => $siswa['nama'],
            "saldo" => $siswa['saldo']
        ]);
    } else {
        echo "UID tidak ditemukan."; // Pesan jika UID tidak ditemukan
    }

    $stmt->close();
} else {
    echo "UID tidak ada."; // Pesan jika UID tidak ada dalam permintaan
}

$conn->close();
?>
