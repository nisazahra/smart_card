<?php
include 'dbconn.php';
$conn = dbconn();

$id_topup = $_GET['id_topup']; // Ambil ID siswa dari URL
echo $id_topup;

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['id_topup'])) {
    // $id_topup = intval($_POST['id_topup']); // Validasi ID sebagai integer
    $id_topup = $_POST['id_topup'];
    $jumlah_topup = $_POST['jumlah_topup']; // Pastikan jumlah adalah angka

    // if ($jumlah_topup < 0) {
    //     echo "Jumlah top-up tidak valid.";
    //     exit;
    // }

    // Ambil jumlah top-up lama dan UID kartu
    $stmt_old = $conn->prepare("SELECT jumlah_topup, uid_kartu FROM tabel_topup WHERE id_topup = ?");
    $stmt_old->bind_param("i", $id_topup);
    $stmt_old->execute();
    $result = $stmt_old->get_result();

    if ($row = $result->fetch_assoc()) {
        $old_amount = $row['jumlah_topup'];
        $uid_kartu = $row['uid_kartu'];

        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // Update jumlah top-up
            $stmt_update = $conn->prepare("UPDATE tabel_topup SET jumlah_topup = ? WHERE id_topup = ?");
            $stmt_update->bind_param("di", $jumlah_topup, $id_topup);
            $stmt_update->execute();

            // Hitung selisih saldo
            $saldo_diff = $jumlah_topup - $old_amount;

            // Update saldo siswa
            $stmt_saldo = $conn->prepare("UPDATE tabel_siswa SET saldo = saldo + ? WHERE uid_kartu = ?");
            $stmt_saldo->bind_param("ds", $saldo_diff, $uid_kartu);
            $stmt_saldo->execute();

            // Ambil ID siswa
            $stmt = $conn->prepare("SELECT id_siswa FROM tabel_siswa WHERE uid_kartu = ?");
            $stmt->bind_param("s", $uid_kartu);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $id_siswa = $row['id_siswa'];
            } else {
                throw new Exception("ID siswa tidak ditemukan.");
            }

            // Commit transaksi
            $conn->commit();

            // Redirect ke halaman riwayat transaksi
            header("Location: riwayat_transaksi_siswa.php?id=" . urlencode($id_siswa));
            exit;
        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            $conn->rollback();
            echo "Terjadi kesalahan: " . $e->getMessage();
            exit;
        }
    } else {
        echo "Data top-up tidak ditemukan.";
        exit;
    }
} else if (isset($_GET['id'])) {
    $id_topup = $_GET['id'];
} else {
    echo "Permintaan tidak valid.";
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Top-Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Edit Top-Up</h1>
        <form action="edit_topup.php" method="POST">
            <!-- Input ID Top-Up (Hidden jika tidak diperlukan pengguna) -->
            <input type="hidden" name="id_topup" id="id_topup" value="<?php echo htmlspecialchars($_GET['id_topup'] ?? ''); ?>">

            <div class="form-group">
                <label for="jumlah_topup">Jumlah Top-Up Baru:</label>
                <input type="number" step="0.01" class="form-control" name="jumlah_topup" id="jumlah_topup" placeholder="Masukkan jumlah top-up baru" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="riwayat_transaksi_siswa.php?id_siswa=<?php echo htmlspecialchars($_GET['id_siswa'] ?? ''); ?>" class="btn btn-secondary">Batal</a>

            <!-- <a href="riwayat_transaksi_siswa.php?id=<?php echo htmlspecialchars($_GET['id_siswa'] ?? ''); ?>" class="btn btn-secondary">Batal</a> -->
        </form>
    </div>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
