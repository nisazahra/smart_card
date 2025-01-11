<?php
include 'dbconn.php';
$conn = dbconn();

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id_siswa = $_GET['id'];

// Query untuk mengambil data siswa berdasarkan ID
$stmt_siswa = $conn->prepare("SELECT * FROM tabel_siswa WHERE id_siswa = ?");
$stmt_siswa->bind_param("s", $id_siswa);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();

if ($result_siswa->num_rows > 0) {
    $data = $result_siswa->fetch_assoc(); // Ambil data siswa
} else {
    echo "Data siswa tidak ditemukan!";
    exit;
}

$stmt_siswa->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>
    <a href="home.php" class="btn btn-outline-primary">Back</a>
    <div class="container text-center mt-4">
        <h1 class="mb-4">Transaction History</h1>
        <p>ID Siswa: <?php echo htmlspecialchars($data['id_siswa']); ?> |
        UID Kartu: <?php echo htmlspecialchars($data['uid_kartu']); ?> |
        Saldo: <?php echo htmlspecialchars($data['saldo']); ?></p>
    </div>
    <div class="container-fluid mt-4">
        <h3 class="mb-3">Student Transaction History (Expenses)</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th style="width: 25%">Waktu Transaksi</th>
                    <th style="width: 20%">Total Transaksi</th>
                    <th style="width: 20%">No. Kantin</th>
                    <th style="width: 20%">Menu</th> <!-- Kolom Menu -->
                    <th style="width: 15%">Quantity</th> <!-- Kolom Quantity -->
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // Query untuk mengambil transaksi beserta Menu dan Quantity
                $stmt_trans = $conn->prepare("SELECT t.waktu_transaksi, t.total_transaksi, t.no_kantin, m.nama_menu, dt.quantity
                                            FROM tabel_transaksi t 
                                            JOIN tabel_siswa s ON t.uid_kartu = s.uid_kartu 
                                            LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                                            LEFT JOIN tabel_menu m ON dt.id_menu = m.id_menu
                                            WHERE s.id_siswa = ?");
                $stmt_trans->bind_param("s", $id_siswa);
                $stmt_trans->execute();
                $result_trans = $stmt_trans->get_result();

                if ($result_trans->num_rows > 0) {
                    while ($row = $result_trans->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu_transaksi']) . "</td>
                                <td>" . htmlspecialchars($row['total_transaksi']) . "</td>
                                <td>" . htmlspecialchars($row['no_kantin']) . "</td>
                                <td>" . htmlspecialchars($row['nama_menu']) . "</td> <!-- Menampilkan Menu -->
                                <td>" . htmlspecialchars($row['quantity']) . "</td> <!-- Menampilkan Quantity -->
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No Transaction Found</td></tr>";
                }

                $stmt_trans->close();
                ?>
            </tbody>
        </table>

        <h3 class="mb-3">Student Top Up History (Income)</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th style="width: 40%">Waktu Top Up</th>
                    <th style="width: 30%">Jumlah Top Up</th>
                    <th style="width: 30%">Admin</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // Query untuk mengambil top-up siswa
                $stmt_topup = $conn->prepare("SELECT t.jumlah_topup, t.waktu_topup, t.admin 
                                              FROM tabel_topup t 
                                              JOIN tabel_siswa s ON t.uid_kartu = s.uid_kartu 
                                              WHERE s.id_siswa = ?");
                $stmt_topup->bind_param("s", $id_siswa);
                $stmt_topup->execute();
                $result_topup = $stmt_topup->get_result();

                if ($result_topup->num_rows > 0) {
                    while ($row = $result_topup->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu_topup']) . "</td>
                                <td>" . htmlspecialchars($row['jumlah_topup']) . "</td>
                                <td>" . htmlspecialchars($row['admin']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No Transaction Found</td></tr>";
                }

                $stmt_topup->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
