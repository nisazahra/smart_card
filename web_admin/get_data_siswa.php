<?php
    // Import koneksi database
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Koneksi database gagal."]));
    }

    // Ambil UID dari request POST
    // $uid_kartu = '13BE2E28';


    if (isset($_POST['uid_kartu'])) {
        $uid_kartu = $_POST['uid_kartu'];
        error_log("UID diterima: " . $uid_kartu); // Log untuk debugging
    } else {
        error_log("UID tidak diterima"); // Log jika UID tidak diterima
    }
    
    // $uid_kartu = isset($_POST['uid_kartu']) ? trim($_POST['uid_kartu']) : "";

    if (empty($uid_kartu)) {
    echo json_encode(["status" => "error", "message" => "UID kartu tidak ditemukan."]);
    exit;
    }

    // Query untuk mencari data siswa berdasarkan UID
    $query = "SELECT id_siswa, nama, saldo FROM tabel_siswa WHERE uid_kartu = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $uid_kartu);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
    $siswa = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => $siswa
    ]);
    } else {
    echo json_encode(["status" => "error", "message" => "UID kartu tidak terdaftar."]);
    }

    $stmt->close();
    $conn->close();
?>
