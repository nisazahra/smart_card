<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $no_kantin = $_GET['no'];

    // Query untuk mengambil data penjual berdasarkan No. Kantin
    $stmt_penjual = $conn->prepare("SELECT * FROM tabel_penjual WHERE no_kantin = ?");
    $stmt_penjual->bind_param("s", $no_kantin);
    $stmt_penjual->execute();
    $result_penjual = $stmt_penjual->get_result();

    if ($result_penjual->num_rows > 0) {
        $data_penjual = $result_penjual->fetch_assoc();
    } else {
        echo "Data penjual tidak ditemukan!";
        exit;
    }


    // Hitung total pemasukan
    $sqlTotalPemasukan = "SELECT SUM(total_transaksi) AS total_pemasukan FROM tabel_transaksi WHERE no_kantin = ?";
    $stmtTotalPemasukan = $conn->prepare($sqlTotalPemasukan);
    $stmtTotalPemasukan->bind_param("i", $no_kantin);
    $stmtTotalPemasukan->execute();
    $resultTotalPemasukan = $stmtTotalPemasukan->get_result();
    $totalPemasukan = $resultTotalPemasukan->fetch_assoc()['total_pemasukan'] ?? 0;

    // Hitung total penarikan
    $sqlTotalPenarikan = "SELECT SUM(jumlah_penarikan) AS total_penarikan FROM tabel_penarikan WHERE no_kantin = ?";
    $stmtTotalPenarikan = $conn->prepare($sqlTotalPenarikan);
    $stmtTotalPenarikan->bind_param("i", $no_kantin);
    $stmtTotalPenarikan->execute();
    $resultTotalPenarikan = $stmtTotalPenarikan->get_result();
    $totalPenarikan = $resultTotalPenarikan->fetch_assoc()['total_penarikan'] ?? 0;

    // Hitung saldo saat ini
    $saldoSaatIni = $totalPemasukan - $totalPenarikan;
    
    $stmt_penjual->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Transaksi Penjual</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <a href="home_penjual.php" class="btn btn-outline-primary"><--</a>
    <div class="container text-center mt-4">
        <h1 class="mb-4">Mutasi Saldo Penjual</h1>
        <p>No. Kantin: <?php echo htmlspecialchars($data_penjual['no_kantin']); ?> ~
        Nama Penjual: <?php echo htmlspecialchars($data_penjual['username']); ?> ~
        Saldo Saat Ini: Rp<?php echo number_format($saldoSaatIni); ?></p>

    </div>
    <div class="container-fluid my-4">
        <h3 class="mb-3">Riwayat Transaksi</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Waktu Transaksi</th>
                    <th>Total Transaksi</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // Query untuk riwayat transaksi
                $stmt_trans = $conn->prepare("SELECT waktu_transaksi, total_transaksi 
                                              FROM tabel_transaksi 
                                              WHERE no_kantin = ?");
                $stmt_trans->bind_param("s", $no_kantin);
                $stmt_trans->execute();
                $result_trans = $stmt_trans->get_result();

                if ($result_trans->num_rows > 0) {
                    while ($row = $result_trans->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu_transaksi']) . "</td>
                                <td>Rp" . number_format($row['total_transaksi'], 2, ',', '.') . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>Tidak ada transaksi ditemukan</td></tr>";
                }
                $stmt_trans->close();
                ?>
            </tbody>
        </table>

        <h3 class="mt-5 mb-3">Riwayat Penarikan Saldo</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Waktu Penarikan</th>
                    <th>Jumlah Penarikan</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // Query untuk riwayat penarikan saldo
                $stmt_penarikan = $conn->prepare("SELECT waktu_penarikan, jumlah_penarikan 
                                                  FROM tabel_penarikan 
                                                  WHERE no_kantin = ?");
                $stmt_penarikan->bind_param("s", $no_kantin);
                $stmt_penarikan->execute();
                $result_penarikan = $stmt_penarikan->get_result();

                if ($result_penarikan->num_rows > 0) {
                    while ($row = $result_penarikan->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu_penarikan']) . "</td>
                                <td>Rp" . number_format($row['jumlah_penarikan'], 2, ',', '.') . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>Tidak ada penarikan saldo ditemukan</td></tr>";
                }
                $stmt_penarikan->close();
                ?>
            </tbody>
        </table>
    </div>

    



    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>


