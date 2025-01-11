
<?php
include 'dbconn.php';
$conn = dbconn();

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    echo "Parameter ID tidak ditemukan!";
    exit;
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
    <a href="home_siswa.php" class="btn btn-outline-primary"><--</a>
    <div class="container text-center mt-4">
        <h1 class="mb-4">Mutasi Saldo Siswa</h1>
        <p>ID Siswa: <?php echo htmlspecialchars($data['id_siswa']); ?> |
        UID Kartu: <?php echo htmlspecialchars($data['uid_kartu']); ?> |
        Saldo: <?php echo htmlspecialchars($data['saldo']); ?></p>
    </div>
    <div class="container-fluid my-4">
        <h3 class="mb-3">Riwayat Transaksi Siswa (Pengeluaran)</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Waktu Transaksi</th>
                    <th>Total Transaksi</th>
                    <th>No. Kantin</th>
                    <th>Menu</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $stmt_trans = $conn->prepare("SELECT 
                                                t.waktu_transaksi, 
                                                t.total_transaksi, 
                                                t.no_kantin, 
                                                m.nama_menu, 
                                                dt.quantity 
                                            FROM 
                                                tabel_transaksi t
                                            JOIN 
                                                tabel_siswa s ON t.uid_kartu = s.uid_kartu
                                            JOIN 
                                                detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                                            JOIN 
                                                tabel_menu m ON dt.id_menu = m.id_menu
                                            WHERE 
                                                s.id_siswa = ?");
                $stmt_trans->bind_param("s", $id_siswa);
                $stmt_trans->execute();
                $result_trans = $stmt_trans->get_result();

                if ($result_trans->num_rows > 0) {
                    while ($row = $result_trans->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['waktu_transaksi']) . "</td>
                                <td>" . htmlspecialchars($row['total_transaksi']) . "</td>
                                <td>" . htmlspecialchars($row['no_kantin']) . "</td>
                                <td>" . htmlspecialchars($row['nama_menu']) . "</td>
                                <td>" . htmlspecialchars($row['quantity']) . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada transaksi</td></tr>";
                }

                $stmt_trans->close();
                ?>
            </tbody>

        </table>
        <h3 class="mb-3">Riwayat Top Up Siswa (Pemasukan)</h3>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Waktu Top Up</th>
                    <th>Jumlah Top Up</th>
                    <th>Admin</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $stmt_topup = $conn->prepare("SELECT t.jumlah_topup, t.waktu_topup, t.admin, t.id_topup 
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
                                <td>
                                    <a href='edit_topup.php?id_topup={$row['id_topup']}'>Edit</a> | 
                                    <a href='delete_topup.php?id={$row['id_topup']}'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada top-up</td></tr>";
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
