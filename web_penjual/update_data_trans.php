<?php
// include 'dbconn.php';
// $conn = dbconn();

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateTransaction'])) {
//     $no_transaksi = $_POST['no_transaksi'];
//     $waktu_transaksi = $_POST['waktu_transaksi'];
//     $uid_kartu = $_POST['uid_kartu'];
//     $total_transaksi = $_POST['total_transaksi'];

//     $sqlUpdate = "UPDATE tabel_transaksi SET waktu_transaksi = ? WHERE no_transaksi = ?";
//     $stmtUpdate = $conn->prepare($sqlUpdate);
//     $stmtUpdate->bind_param("ssdi", $waktu_transaksi, $uid_kartu, $total_transaksi, $no_transaksi);

//     if ($stmtUpdate->execute()) {
//         header("Location: home.php?success=update");
//         exit;
//     } else {
//         echo "Gagal memperbarui transaksi.";
//     }
//     $stmtUpdate->close();
// }
// $conn->close();
include 'dbconn.php';
$conn = dbconn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateTransaction'])) {
    $no_transaksi = $_POST['no_transaksi'];
    $waktu_transaksi = $_POST['waktu_transaksi'];
    $uid_kartu = $_POST['uid_kartu'];
    $total_transaksi = $_POST['total_transaksi'];

    // Ambil transaksi lama untuk menghitung selisih
    $stmtOldTrans = $conn->prepare("SELECT total_transaksi FROM tabel_transaksi WHERE no_transaksi = ?");
    $stmtOldTrans->bind_param("s", $no_transaksi);
    $stmtOldTrans->execute();
    $resultOldTrans = $stmtOldTrans->get_result();
    if ($resultOldTrans->num_rows > 0) {
        $oldTrans = $resultOldTrans->fetch_assoc();
        $oldTotalTransaksi = $oldTrans['total_transaksi'];
    } else {
        echo "Transaksi tidak ditemukan!";
        exit;
    }
    $stmtOldTrans->close();

    // Hitung selisih transaksi (perbedaan antara transaksi baru dan transaksi lama)
    $selisihTransaksi = $oldTotalTransaksi - $total_transaksi;

    // Update transaksi
    $sqlUpdate = "UPDATE tabel_transaksi SET waktu_transaksi = ?, total_transaksi = ? WHERE no_transaksi = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssd", $waktu_transaksi, $total_transaksi, $no_transaksi);

    if ($stmtUpdate->execute()) {
        // Ambil saldo siswa yang terkait dengan UID kartu
        $stmtSaldo = $conn->prepare("SELECT saldo FROM tabel_siswa WHERE uid_kartu = ?");
        $stmtSaldo->bind_param("s", $uid_kartu);
        $stmtSaldo->execute();
        $resultSaldo = $stmtSaldo->get_result();
        if ($resultSaldo->num_rows > 0) {
            $saldoSiswa = $resultSaldo->fetch_assoc()['saldo'];
        } else {
            echo "Saldo siswa tidak ditemukan!";
            exit;
        }
        $stmtSaldo->close();

        // Update saldo siswa berdasarkan selisih transaksi
        $newSaldo = $saldoSiswa + $selisihTransaksi; // Tambah atau kurangi sesuai dengan selisih transaksi
        $sqlUpdateSaldo = "UPDATE tabel_siswa SET saldo = ? WHERE uid_kartu = ?";
        $stmtUpdateSaldo = $conn->prepare($sqlUpdateSaldo);
        $stmtUpdateSaldo->bind_param("ds", $newSaldo, $uid_kartu);

        if ($stmtUpdateSaldo->execute()) {
            header("Location: home.php?success=update");
            exit;
        } else {
            echo "Gagal memperbarui saldo siswa.";
        }
        $stmtUpdateSaldo->close();
    } else {
        echo "Gagal memperbarui transaksi.";
    }
    $stmtUpdate->close();
}

$conn->close();

