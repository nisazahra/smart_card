<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $no_kantin = $_GET['no']; // Ambil No Kantin dari URL
    $id_menu = "";
    $nama_menu = "";
    $harga = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        // Ambil data dari form
        $id_menu = $_POST["id_menu"];
        $nama_menu = $_POST["nama_menu"];
        $harga = $_POST["harga"];

        // Query untuk memasukkan data ke tabel_menu
        $sql = "INSERT INTO tabel_menu (no_kantin, id_menu, nama_menu, harga) VALUES ('$no_kantin','$id_menu', '$nama_menu', '$harga')";

        if ($conn->query($sql) === TRUE) {
            // Redirect setelah data berhasil disimpan
            header("Location: menu.php?no=$no_kantin&success=update");
            exit(); // Menghentikan eksekusi setelah redirect
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Daftar Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Tambah Daftar Menu</h1>

        <!-- Form untuk tambah menu -->
        <form id="transaksiForm" class="p-4 border rounded shadow-sm bg-light" method="POST">
            <div class="form-group">
                <label for="no_kantin">No Kantin:</label>
                <input type="text" name="no_kantin" id="no_kantin" class="form-control" value="<?php echo $no_kantin?>" readonly>
            </div>
            <div class="form-group">
                <label for="nama_menu">Nama Menu:</label>
                <input type="text" name="nama_menu" id="nama_menu" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="number" name="harga" id="harga" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
            <!-- Tombol cancel yang mengarah ke menu.php dengan parameter no_kantin -->
            <a href="menu.php?no=<?php echo $no_kantin; ?>" class="btn btn-outline-secondary btn-block">Cancel</a>
        </form>
    </div>
</body>
</html>
