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
    <div class="container text-center mt-5">
        <h1 class="mb-4">Tracking Transaksi Penjual</h1>
        <p>No. Kantin: <?php echo htmlspecialchars($data_penjual['no_kantin']); ?> ~
        Nama Penjual: <?php echo htmlspecialchars($data_penjual['username']); ?> ~
        Saldo Saat Ini: Rp<?php echo number_format($saldoSaatIni); ?></p>

    </div>

    <div class="container-fluid my-4">
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Waktu Transaksi</th>
                    <th>Jumlah</th>
                    <th>Jenis Transaksi</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // Gabungkan riwayat transaksi dan penarikan saldo
                $stmt = $conn->prepare("(
                    SELECT waktu_transaksi AS waktu, total_transaksi AS jumlah, 'Pemasukan' AS jenis_transaksi
                    FROM tabel_transaksi
                    WHERE no_kantin = ?
                    UNION ALL
                    SELECT waktu_penarikan AS waktu, jumlah_penarikan AS jumlah, 'Penarikan' AS jenis_transaksi
                    FROM tabel_penarikan
                    WHERE no_kantin = ?
                ) ORDER BY waktu DESC"); // Mengurutkan berdasarkan waktu
                $stmt->bind_param("ss", $no_kantin, $no_kantin);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu']) . "</td>
                                <td>Rp" . number_format($row['jumlah'], 2, ',', '.') . "</td>
                                <td>" . htmlspecialchars($row['jenis_transaksi']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Tidak ada transaksi atau penarikan saldo ditemukan</td></tr>";
                }
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>


