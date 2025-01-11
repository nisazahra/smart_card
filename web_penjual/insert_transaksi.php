<?php
include 'dbconn.php';
$conn = dbconn();

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$no_kantin = isset($_GET['no_kantin']) ? $_GET['no_kantin'] : "";

// $menuQuery = "SELECT * FROM tabel_menu WHERE no_kantin= $no_kantin";
// $menuResult = $conn->query($menuQuery);

// Query data menu sekali dan simpan hasilnya dalam array
$menuQuery = "SELECT * FROM tabel_menu WHERE no_kantin= $no_kantin";
$menuResult = $conn->query($menuQuery);
$menuData = [];
while ($menu = $menuResult->fetch_assoc()) {
    $menuData[] = $menu;
}

// Handle transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid_kartu = $_POST['uid_kartu'];
    $total_transaksi = $_POST['total_transaksi'];
    $waktu_transaksi = $_POST['waktu_transaksi'];
    $menuItems = $_POST['menu'];
    $jumlahItems = $_POST['jumlah'];

    if (empty($uid_kartu) || empty($total_transaksi) || empty($waktu_transaksi) || empty($menuItems) || empty($jumlahItems)) {
        echo "<script>alert('Semua data wajib diisi.');</script>";
        exit;
    }

    // Validasi saldo kartu
    $query = "SELECT saldo FROM tabel_siswa WHERE uid_kartu = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $uid_kartu);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $saldo = $user['saldo'];

        if ($saldo >= $total_transaksi) {
            // Potong saldo
            $new_saldo = $saldo - $total_transaksi;
            $update_saldo = "UPDATE tabel_siswa SET saldo = ? WHERE uid_kartu = ?";
            $stmt = $conn->prepare($update_saldo);
            $stmt->bind_param("ds", $new_saldo, $uid_kartu);
            $stmt->execute();

            // Insert transaksi utama
            $insert_transaksi = "INSERT INTO tabel_transaksi (no_kantin, uid_kartu, waktu_transaksi, total_transaksi) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_transaksi);
            $stmt->bind_param("issd", $no_kantin, $uid_kartu, $waktu_transaksi, $total_transaksi);
            $stmt->execute();

            $id_transaksi = $conn->insert_id;

            foreach ($menuItems as $index => $id_menu) {
                $jumlah = $jumlahItems[$index];
                $insert_detail = "INSERT INTO detail_transaksi (id_transaksi, id_menu, quantity) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_detail);
                $stmt->bind_param("iii", $id_transaksi, $id_menu, $jumlah);
                $stmt->execute();
            }

            // Redirect dengan pesan sukses
            echo "<script>alert('Transaksi berhasil. Sisa saldo: Rp$new_saldo'); window.location.href='home.php';</script>";
        } else {
            echo "<script>alert('Saldo tidak mencukupi.');</script>";
        }
    } else {
        echo "<script>alert('UID tidak terdaftar.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Transaksi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const noKantin = <?= json_encode($no_kantin); ?>;

            setInterval(function() {
                const uidContainerUrl = `UID_container_${noKantin}.php`;
                $.get(uidContainerUrl, function(data) {
                    const uid_kartu = data.trim();
                    $("#uid_kartu").val(uid_kartu);

                    const siswaUrl = `get_data_siswa_${noKantin}.php?uid_kartu=${uid_kartu}`;
                    $.get(siswaUrl, function(siswaData) {
                        try {
                            const parsedData = JSON.parse(siswaData);
                            if (parsedData.nama && parsedData.saldo) {
                                $("#nama").val(parsedData.nama);
                                $("#saldo").val(parsedData.saldo);
                            } else {
                                alert("Data siswa tidak lengkap atau UID tidak ditemukan.");
                            }
                        } catch (e) {
                            console.error("Error parsing JSON:", e);
                            alert("Gagal memproses data siswa dari server.");
                        }
                    });
                });
            }, 500);
        });

        function tambahItem(){
            const newItem = `
            <div class="item-row d-flex align-items-center mb-2">
                <select name="menu[]" class="menu-select form-control mr-2" onchange="hitungTotal()" required>
                    <option value="">--Pilih Menu--</option>
                    <?php foreach ($menuData as $menu): ?>
                        <option value="<?= $menu['id_menu']; ?>" data-harga="<?= $menu['harga']; ?>">
                            <?= $menu['nama_menu']; ?> - Rp<?= $menu['harga']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="jumlah[]" class="jumlah-input form-control mr-2" value="1" min="1" onchange="hitungTotal()" required>
                <button type="button" class="btn btn-danger" onclick="hapusItem(this)">Hapus</button>
            </div>
            `;
            $("#menu-container").append(newItem);
        }

        function hapusItem(button) {
            $(button).closest(".item-row").remove();
            hitungTotal();
        }

        function hitungTotal() {
            let total = 0;
            $(".item-row").each(function() {
                const harga = $(this).find(".menu-select option:selected").data("harga") || 0;
                const jumlah = $(this).find(".jumlah-input").val() || 0;
                total += harga * jumlah;
            });
            $("#total_transaksi").val(total);
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Insert Transaction</h1>
        <form id="transaksiForm" class="p-4 border rounded shadow-sm bg-light" method="POST">
            <div class="form-group">
                <label for="no_kantin">No Kantin:</label>
                <input type="text" name="no_kantin" class="form-control" value="<?= $no_kantin; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="uid_kartu">UID Kartu:</label>
                <input type="text" name="uid_kartu" id="uid_kartu" class="form-control" placeholder="Please scan your card..." readonly>
            </div>
            <div class="form-group">
                <label for="nama">Nama Siswa:</label>
                <input type="text" name="nama" id="nama" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="saldo">Saldo Siswa:</label>
                <input type="text" name="saldo" id="saldo" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="waktu_transaksi">Waktu dan Tanggal Transaksi:</label>
                <input type="datetime-local" name="waktu_transaksi" id="waktu_transaksi" class="form-control" required>
            </div>
            <div id="menu-container" class="form-group">
                <label>Pesanan:</label>
                <div class="item-row d-flex align-items-center mb-2">
                    <select name="menu[]" class="menu-select form-control mr-2" onchange="hitungTotal()" required>
                        <option value="">-- Pilih Menu --</option>
                        <?php foreach ($menuData as $menu): ?>
                            <option value="<?= $menu['id_menu']; ?>" data-harga="<?= $menu['harga']; ?>">
                                <?= $menu['nama_menu']; ?> - Rp<?= $menu['harga']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="jumlah[]" class="jumlah-input form-control mr-2" value="1" min="1" onchange="hitungTotal()" required>
                    <button type="button" class="btn btn-danger" onclick="hapusItem(this)">Hapus</button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="tambahItem()">+ Tambah item pesanan</button>
            <div class="form-group">
                <label for="total_transaksi">Total Transaksi:</label>
                <input type="number" name="total_transaksi" id="total_transaksi" class="form-control" readonly>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
            <a href="home.php" class="btn btn-outline-secondary btn-block">Cancel</a>
        </form>
    </div>
</body>
</html>