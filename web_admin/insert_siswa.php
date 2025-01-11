<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $id_siswa = "";
    $uid_kartu = "";
    $nama = "";
    $saldo = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $id_siswa = $_POST["id_siswa"];
        $uid_kartu = $_POST["uid_kartu"];
        $nama = $_POST["nama"];
        $saldo = $_POST["saldo"];

        $sql = "INSERT INTO tabel_siswa (id_siswa, uid_kartu, nama, saldo) VALUES ('$id_siswa','$uid_kartu', '$nama', '$saldo')";

        if ($conn->query($sql) === TRUE) {
            // echo "Data berhasil ditambahkan!";
            header("Location: home_siswa.php");
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
    <title>Insert Data Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            setInterval(function() {
                $.get("UID_container.php", function(data) {
                    console.log("UID Data:", data); // Debugging: lihat nilai UID di console
                    $("#uid_kartu").val(data.trim()); // Isi input dengan data UID
                });
            }, 500); // Update setiap 500ms
        });

    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Tambah Data Siswa</h1>

        <!-- Display UID for debugging -->
        <!-- <p id="uidDisplay" class="alert alert-info text-center">Please scan your card...</p> -->

        <form id="transaksiForm" class="p-4 border rounded shadow-sm bg-light" method="POST">
            <div class="form-group">
                <label for="id_siswa">ID Siswa:</label>
                <input type="text" name="id_siswa" id="id_siswa" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="uid_kartu">UID Kartu:</label>
                <input type="text" name="uid_kartu" id="uid_kartu" class="form-control" placeholder="Please scan your card..." readonly>
            </div>
            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" name="nama" id="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="saldo">Saldo:</label>
                <input type="number" name="saldo" id="saldo" class="form-control" placeholder="silahkan lakukan topup setelah input data siswa" readonly>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
            <a href="home_siswa.php" class="btn btn-outline-secondary btn-block">Cancel</a>
        </form>
    </div>
</body>
</html>
