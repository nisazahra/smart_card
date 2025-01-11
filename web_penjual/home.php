<?php
    session_start();

    // Periksa apakah pengguna sudah login
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }

    include 'dbconn.php'; 
    $conn = dbconn();

    $username = $_SESSION['username'];

    // Query user untuk mendapatkan nomor kantin dan pemasukan
    $sql1 = "SELECT * FROM tabel_penjual WHERE username = ?";
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $no_kantin = $user['no_kantin'];
        // $pemasukan = $user['pemasukan'];
    } else {
        echo "Data user tidak ditemukan.";
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


    // Tambahkan transaksi baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addTransaction'])) {
        $waktu_transaksi = $_POST['waktu_transaksi'];
        $uid_kartu = $_POST['uid_kartu'];
        $total_transaksi = $_POST['total_transaksi'];

        // Ekstrak tanggal dari waktu transaksi
        $tanggal_transaksi = date('Y-m-d', strtotime($waktu_transaksi));

        $sqlCount = "SELECT COUNT(*) AS count_trans FROM tabel_transaksi WHERE no_kantin = ? AND DATE(waktu_transaksi) = ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("is", $no_kantin, $tanggal_transaksi);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $countTrans = $resultCount->fetch_assoc()['count_trans'] + 1; // Increment count

        //format nomor transaksi
        $kode_transaksi = sprintf("T%s%s%03d", $no_kantin, date('dmy', strtotime($tanggal_transaksi)), $countTrans);
        $sqlInsert = "INSERT INTO tabel_transaksi (no_transaksi, no_kantin, waktu_transaksi, uid_kartu, total_transaksi) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sissd", $kode_transaksi, $no_kantin, $waktu_transaksi, $uid_kartu, $total_transaksi);
        
        if ($stmtInsert->execute()) {
            header("Location: home.php?success=add");
            exit;
        } else {
            echo "Gagal menambahkan transaksi.";
        }
        $stmtCount->close();
        $stmtInsert->close();
        
    }

    // Query transaksi dengan filter tanggal (jika ada)
    $transaction_date = isset($_GET['transaction_date']) ? $_GET['transaction_date'] : '';
    $query = "SELECT id_transaksi, waktu_transaksi, uid_kartu, total_transaksi FROM tabel_transaksi WHERE no_kantin = ?";
    if (!empty($transaction_date)) {
        $query .= " AND DATE(waktu_transaksi) = ?";
    }

    $stmt2 = $conn->prepare($query);
    if (!empty($transaction_date)) {
        $stmt2->bind_param("is", $no_kantin, $transaction_date);
    } else {
        $stmt2->bind_param("i", $no_kantin);
    }
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Penjual</title>
    <!-- Link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid my-3">
        <table class="w-100">
            <tr>
                <td>
                    <h2>Selamat datang, <?php echo htmlspecialchars($username); ?>!</h2>
                    <h3>No Kantin: <?php echo htmlspecialchars($no_kantin); ?></h3>
                    <h4>Saldo Saat Ini: Rp<?php echo number_format($saldoSaatIni, 2, ',', '.'); ?></h4>

                    <!-- <p>Pemasukan: Rp<?php echo number_format($pemasukan, 2, ',', '.'); ?></p> -->
                </td>
                <td class="text-center" style="float: right;">
                    <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
                </td>
            </tr>
        </table>

    <div class="container-fluid my-4">
        <form class="form-inline d-flex align-items-center">
            <div class="input-group w-100 me-2">
                <!-- <input class="form-control me-2" type="search" name="search" placeholder="Cari berdasarkan Nomor Kantin" aria-label="Search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"> -->
                <input class="form-control me-2" type="date" name="transaction_date" aria-label="Transaction Date" value="<?php echo isset($_GET['transaction_date']) ? $_GET['transaction_date'] : ''; ?>">
                <button class="btn btn-outline-primary " type="submit">Search</button>
                <a href="insert_transaksi.php?no_kantin=<?php echo urlencode($no_kantin);?>" class="btn btn-primary" style="margin-left: 5px;">+</a>
                <!-- <a href="insert_transaksi.php" class="btn btn-primary" style="margin-left: 5px;">+</a> -->
            </div>
        </form>
        
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>No Transaksi</th>
                    <th>Waktu Transaksi</th>
                    <th>UID Kartu</th>
                    <th>Jumlah Transaksi</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                    if ($result2->num_rows > 0) {
                        while ($row = $result2->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id_transaksi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['waktu_transaksi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['uid_kartu']) . "</td>";
                            echo "<td>Rp" . number_format($row['total_transaksi'], 2, ',', '.') . "</td>";
                            echo "<td>
                                <button 
                                    type='button' 
                                    class='btn btn-warning edit-transaction-btn'
                                    data-no-trans='" . htmlspecialchars($row['id_transaksi']) . "'
                                    data-waktu-trans='" . htmlspecialchars($row['waktu_transaksi']) . "'
                                    data-uid-kartu='" . htmlspecialchars($row['uid_kartu']) . "'
                                    data-total-transaksi='" . htmlspecialchars($row['total_transaksi']) . "'>
                                    Edit
                                </button>
                                <a href='delete_trans.php?no=" . htmlspecialchars($row['id_transaksi']) . "' class='btn btn-danger' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Tidak ada data transaksi.</td></tr>";
                    }
                    ?>
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="updateTransactionModal" tabindex="-1" aria-labelledby="updateTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="update_data_trans.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateTransactionModalLabel">Edit Transaksi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_no_transaksi" name="no_transaksi">
                        <div class="mb-3">
                            <label for="edit_waktu_transaksi" class="form-label">Waktu Transaksi</label>
                            <input type="datetime-local" class="form-control" id="edit_waktu_transaksi" name="waktu_transaksi" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_uid_kartu" class="form-label">UID Kartu</label>
                            <input type="text" class="form-control" id="edit_uid_kartu" name="uid_kartu" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_transaksi" class="form-label">Jumlah Transaksi</label>
                            <input type="number" step="0.01" class="form-control" id="edit_total_transaksi" name="total_transaksi" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="updateTransaction" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="successModalContainer"></div>
    
    <script>
        // Event listener untuk mengisi modal Edit dengan data transaksi
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('edit-transaction-btn')) {
                const noTrans = e.target.dataset.noTrans;
                const waktuTrans = e.target.dataset.waktuTrans;
                const uidKartu = e.target.dataset.uidKartu;
                const totalTransaksi = e.target.dataset.totalTransaksi;

                document.getElementById('edit_no_transaksi').value = noTrans;
                document.getElementById('edit_waktu_transaksi').value = waktuTrans;
                document.getElementById('edit_uid_kartu').value = uidKartu;
                document.getElementById('edit_total_transaksi').value = totalTransaksi;

                const modal = new bootstrap.Modal(document.getElementById('updateTransactionModal'));
                modal.show();
            }
        });

        document.getElementById('btnTapKartu').addEventListener('click', function () {
            $('#tapKartuModal').modal('show');

            const interval = setInterval(function () {
                // Polling server untuk melihat apakah UID diterima
                fetch('check_uid.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'ready') {
                            clearInterval(interval); // Hentikan polling
                            $('#tapKartuModal').modal('hide');

                            if (data.result === 'success') {
                                // Tampilkan modal konfirmasi
                                const modal = `
                                    <div class="modal fade" id="successModal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Transaksi Berhasil</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <p>${data.message}</p>
                                                    <p>Sisa Saldo: Rp ${data.saldo}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" id="goHome">OK</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                document.body.insertAdjacentHTML('beforeend', modal);
                                $('#successModal').modal('show');

                                // Arahkan ke home.php setelah modal ditutup
                                document.getElementById('goHome').addEventListener('click', function () {
                                    location.href = 'home.php';
                                });
                            } else {
                                alert(data.message);
                            }
                        }
                    })
                    .catch(error => {
                        clearInterval(interval);
                        $('#tapKartuModal').modal('hide');
                        alert('Terjadi kesalahan: ' + error.message);
                    });
            }, 2000); // Polling setiap 2 detik
        });

    </script>
    <?php

    $stmt->close();
    $stmt2->close();
    $conn->close();
    ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
