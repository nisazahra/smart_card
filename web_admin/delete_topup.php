<?php
include 'dbconn.php';
$conn = dbconn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_topup'])) {
    $id_topup = intval($_POST['id_topup']); // Validasi ID sebagai integer

    // Ambil data jumlah top-up dan UID kartu
    $stmt_old = $conn->prepare("SELECT jumlah_topup, uid_kartu FROM tabel_topup WHERE id_topup = ?");
    $stmt_old->bind_param("i", $id_topup);
    $stmt_old->execute();
    $result = $stmt_old->get_result();

    if ($row = $result->fetch_assoc()) {
        $amount = $row['jumlah_topup'];
        $uid_kartu = $row['uid_kartu'];

        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // Hapus data top-up
            $stmt_delete = $conn->prepare("DELETE FROM tabel_topup WHERE id_topup = ?");
            $stmt_delete->bind_param("i", $id_topup);
            $stmt_delete->execute();

            // Kurangi saldo siswa
            $stmt_saldo = $conn->prepare("UPDATE tabel_siswa SET saldo = saldo - ? WHERE uid_kartu = ?");
            $stmt_saldo->bind_param("ds", $amount, $uid_kartu);
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
} else {
    echo "Permintaan tidak valid.";
    exit;
}

$conn->close();
?>
