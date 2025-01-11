<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $no_kantin = $_GET['no']; // Ambil ID siswa dari URL

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $pemasukan = $_POST['saldo'];

        $sql = "UPDATE tabel_penjual SET pemasukan='$pemasukan' WHERE no_kantin='$no_kantin'";

        if ($conn->query($sql) === TRUE) {
            header("Location: home_penjual.php?success=update");
            exit(); // Menghentikan eksekusi setelah redirect
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $sql = "SELECT * FROM tabel_penjual WHERE no_kantin='$no_kantin'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Data tidak ditemukan!";
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

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penjual</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

</head>
<body>
    <div class="container mt-5">
    <h1 class="text-center mb-4">Info</h1>
    <form method="POST" class="p-4 border rounded shadow-sm bg-light" action="">
        <div class="form-group">
            <label for="no_kantin">No Kantin:</label>
            <input type="text" name="no_kantin" class="form-control" value="<?php echo $data['no_kantin']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" class="form-control" value="<?php echo $data['username']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="text" name="password" class="form-control" value="<?php echo $data['pass']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="password">Saldo:</label>
            <input type="text" name="password" class="form-control" value="<?php echo$saldoSaatIni; ?>" readonly>
        </div>

        <a href="home_penjual.php" class="btn btn-outline-secondary btn-block">Back</a>
    </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>