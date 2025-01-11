<?php 
    include 'dbconn.php'; 
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Cek apakah parameter 'no' ada di URL
    if (isset($_GET['no']) && !empty($_GET['no'])) {
        $no_kantin = $_GET['no']; // Ambil nomor kantin dari URL
    } else {
        die("ID kantin tidak ditemukan.");
    }

    // Jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil jumlah penarikan dari form
        $jumlah_penarikan = $_POST['jumlah_penarikan'];
        $admin = $_POST['admin'];

        // Cek apakah saldo cukup
        $sqlCekSaldo = "SELECT COALESCE(SUM(tabel_transaksi.total_transaksi), 0) 
         - COALESCE(SUM(tabel_penarikan.jumlah_penarikan), 0) AS saldo
    FROM tabel_transaksi 
    LEFT JOIN tabel_penarikan ON tabel_transaksi.no_kantin = tabel_penarikan.no_kantin 
    WHERE tabel_transaksi.no_kantin = ?";
        $stmtCekSaldo = $conn->prepare($sqlCekSaldo);
        $stmtCekSaldo->bind_param("i", $no_kantin);
        $stmtCekSaldo->execute();
        $resultCekSaldo = $stmtCekSaldo->get_result();
        $saldo = $resultCekSaldo->fetch_assoc()['saldo'] ?? 0;

        if ($saldo >= $jumlah_penarikan) {
            // Masukkan penarikan ke tabel_penarikan
            $sqlInsertPenarikan = "INSERT INTO tabel_penarikan (admin, no_kantin, jumlah_penarikan) VALUES (?, ?, ?)";
            $stmtInsertPenarikan = $conn->prepare($sqlInsertPenarikan);
            $stmtInsertPenarikan->bind_param("id", $no_kantin, $jumlah_penarikan);
            $stmtInsertPenarikan->execute();

            // Redirect ke halaman home_penjual setelah sukses
            header("Location: home_penjual.php?success=update");
            exit(); // Menghentikan eksekusi setelah redirect
        } else {
            // Jika saldo tidak cukup
            echo "<div class='alert alert-danger'>Saldo tidak cukup untuk penarikan tersebut.</div>";
        }
    }

    // Total transaksi
    $sqlTotalTransaksi = "SELECT SUM(total_transaksi) AS total_transaksi FROM tabel_transaksi WHERE no_kantin = ?";
    $stmtTotalTransaksi = $conn->prepare($sqlTotalTransaksi);
    $stmtTotalTransaksi->bind_param("i", $no_kantin);
    $stmtTotalTransaksi->execute();
    $resultTotalTransaksi = $stmtTotalTransaksi->get_result();
    $totalTransaksi = $resultTotalTransaksi->fetch_assoc()['total_transaksi'] ?? 0;

    // Total penarikan
    $sqlTotalPenarikan = "SELECT SUM(jumlah_penarikan) AS total_penarikan FROM tabel_penarikan WHERE no_kantin = ?";
    $stmtTotalPenarikan = $conn->prepare($sqlTotalPenarikan);
    $stmtTotalPenarikan->bind_param("i", $no_kantin);
    $stmtTotalPenarikan->execute();
    $resultTotalPenarikan = $stmtTotalPenarikan->get_result();
    $totalPenarikan = $resultTotalPenarikan->fetch_assoc()['total_penarikan'] ?? 0;

    // Hitung pemasukan akhir
    $pemasukan = $totalTransaksi - $totalPenarikan;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarik Saldo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Tarik Saldo Penjual</h2>
        <form method="POST" action="tarik_saldo.php?no=<?php echo $no_kantin; ?>">
            <p>No Kantin: <?php echo $no_kantin; ?></p>
            <div class="form-group">
                <label for="admin">Admin yang melalukan tarik saldo:</label>
                <input type="text" id="admin" name="admin" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="jumlah_penarikan">Jumlah Penarikan:</label>
                <input type="number" id="jumlah_penarikan" name="jumlah_penarikan" step="0.01" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-secondary">Tarik Saldo</button>
            <a href="home_penjual.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>
