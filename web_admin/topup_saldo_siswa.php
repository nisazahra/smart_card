<?php

include 'dbconn.php'; 
$conn = dbconn();

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mendapatkan ID siswa dari URL jika ada
$id_siswa = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_siswa) {
    echo "<script>alert('ID siswa tidak ditemukan!'); window.location.href='home_siswa.php';</script>";
    exit;
}

// Ambil data siswa berdasarkan ID
$sql2 = "SELECT * FROM tabel_siswa WHERE id_siswa = ?";
$stmt = $conn->prepare($sql2);
$stmt->bind_param("s", $id_siswa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $uid_kartu = $data['uid_kartu'];
    $nama = $data['nama'];
    $saldo = $data['saldo'];
} else {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location.href='home_siswa.php';</script>";
    exit;
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan data dari request (metode POST)
    $jumlah_topup = $_POST['jumlah_topup'];
    $admin = $_POST['admin'];

    // Validasi input
    if (empty($jumlah_topup) || empty($admin)) {
        echo "<script>alert('Data tidak lengkap!');</script>";
    } elseif (!is_numeric($jumlah_topup) || $jumlah_topup <= 0) {
        echo "<script>alert('Jumlah top-up harus berupa angka positif!');</script>";
    } else {
        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // 1. Tambahkan data top-up ke tabel_topup
            $sql_topup = "INSERT INTO tabel_topup (uid_kartu, jumlah_topup, admin) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_topup);
            $stmt->bind_param("sds", $uid_kartu, $jumlah_topup, $admin);
            $stmt->execute();

            // 2. Update saldo siswa di tabel_siswa
            $sql_update_saldo = "UPDATE tabel_siswa SET saldo = saldo + ? WHERE uid_kartu = ?";
            $stmt = $conn->prepare($sql_update_saldo);
            $stmt->bind_param("ds", $jumlah_topup, $uid_kartu);
            $stmt->execute();

            // Commit transaksi
            $conn->commit();
            echo "<script>alert('Top-up berhasil!'); window.location.href='home_siswa.php';</script>";
        } catch (Exception $e) {
            // Rollback jika ada kesalahan
            $conn->rollback();
            echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');</script>";
        }
    }
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up Saldo Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Top Up Saldo Siswa</h1>
        <form method="POST" class="p-4 border rounded shadow-sm bg-light">
            <div class="form-group">
                <label for="uid_kartu">UID Kartu:</label>
                <input type="text" id="uid_kartu" name="uid_kartu" class="form-control" value="<?php echo htmlspecialchars($uid_kartu); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nama">Nama Siswa:</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?php echo htmlspecialchars($nama); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="saldo">Saldo Sekarang:</label>
                <input type="text" id="saldo" name="saldo" class="form-control" value="<?php echo htmlspecialchars($saldo); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="admin">Admin yang melakukan top-up:</label>
                <input type="text" id="admin" name="admin" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="jumlah_topup">Jumlah Top Up:</label>
                <input type="number" id="jumlah_topup" name="jumlah_topup" step="0.01" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
            <a href="home_siswa.php" class="btn btn-outline-secondary btn-block">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>

