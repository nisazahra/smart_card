<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Pastikan parameter id_menu ada di URL
    if (isset($_GET['id'])) {
        $id_menu = $_GET['id'];

        // Ambil data menu berdasarkan id_menu
        $sql = "SELECT * FROM tabel_menu WHERE id_menu='$id_menu'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $no_kantin = $data['no_kantin']; // Ambil no_kantin dari hasil query
        } else {
            echo "Data tidak ditemukan!";
            exit;
        }

        // Proses form submit (POST)
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nama_menu = $_POST['nama_menu'];
            $harga = $_POST['harga'];

            // Prepared statement untuk menghindari SQL injection
            $sql = "UPDATE tabel_menu SET nama_menu=?, harga=? WHERE id_menu=?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssi", $nama_menu, $harga, $id_menu); // Binding parameter
                if ($stmt->execute()) {
                    header("Location: menu.php?no=$no_kantin&success=update");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        }

    } else {
        echo "Parameter tidak lengkap!";
        exit;
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Edit Data Menu</h1>
        <form method="POST" class="p-4 border rounded shadow-sm bg-light" action="">
            <div class="form-group">
                <label for="no_kantin">No Kantin:</label>
                <input type="text" name="no_kantin" class="form-control" value="<?php echo $no_kantin; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nama_menu">Nama Menu:</label>
                <input type="text" name="nama_menu" class="form-control" value="<?php echo $data['nama_menu']; ?>" required>
            </div>
            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="number" name="harga" class="form-control" value="<?php echo $data['harga']; ?>" required>
            </div>

            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
            <a href="home_penjual.php" class="btn btn-outline-secondary btn-block">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>
