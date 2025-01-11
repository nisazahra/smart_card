<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $id_siswa = $_GET['id']; // Ambil ID siswa dari URL

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $uid_kartu = $_POST['uid_kartu'];
        $nama = $_POST['nama'];
        $saldo = $_POST['saldo'];

        $sql = "UPDATE tabel_siswa SET uid_kartu='$uid_kartu', nama='$nama', saldo='$saldo' WHERE id_siswa='$id_siswa'";

        if ($conn->query($sql) === TRUE) {
            header("Location: home_siswa.php?success=update");
            exit(); // Menghentikan eksekusi setelah redirect
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Ambil data siswa berdasarkan ID
    $sql = "SELECT * FROM tabel_siswa WHERE id_siswa='$id_siswa'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Data tidak ditemukan!";
        exit;
    }

    $conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    //     $(document).ready(function(){
    //         setInterval(function() {
    //             $.get("UID_container.php", function(data) {
    //                 console.log("UID Data:", data); // Debugging: lihat nilai UID di console
    //                 $("#uid_kartu").val(data.trim()); // Isi input dengan data UID
    //             });
    //         }, 500); // Update setiap 500ms
    //     });

    // </script>
</head>
<body>
    <div class="container mt-5">
    <h1 class="text-center mb-4">Edit Data Siswa</h1>
    <form method="POST" class="p-4 border rounded shadow-sm bg-light" action="">
        <div class="form-group">
            <label for="uid_kartu">ID Siswa:</label>
            <input type="text" name="id_siswa" class="form-control" value="<?php echo $data['id_siswa']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="uid_kartu">UID Kartu:</label>
            <input type="text" name="uid_kartu" class="form-control" value="<?php echo $data['uid_kartu']; ?>" required>
        </div>
        <div class="form-group">
            <label for="nama">Nama Siswa:</label>
            <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>" required>
        </div>
        <div class="form-group">
            <label for="saldo">Saldo:</label>
            <input type="text" step="0.01" name="saldo" class="form-control" value="<?php echo $data['saldo']; ?>" readonly>
        </div>

        <button type="submit" class="btn btn-secondary btn-block">Submit</button>
        <a href="home_siswa.php" class="btn btn-outline-secondary btn-block">Cancel</a>
    </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>