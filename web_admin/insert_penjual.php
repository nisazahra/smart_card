<?php
    include 'dbconn.php';
    $conn = dbconn();

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $no_kantin = "";
    $username = "";
    $password = "";
    $pemasukan = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $no_kantin = $_POST["no_kantin"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $pemasukan = $_POST["pemasukan"];

        $sql = "INSERT INTO tabel_penjual (no_kantin, username, pass, pemasukan) VALUES ('$no_kantin', '$username', '$password', '$pemasukan')";

        if ($conn->query($sql) === TRUE) {
            // echo "Data berhasil ditambahkan!";
            header("Location: home_penjual.php");
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
    <title>Insert Penjual</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
    <h1 class="text-center mb-4">Tambah Data Penjual</h1>
    <form method="POST" class="p-4 border rounded shadow-sm bg-light" action="">
        <div class="form-group">
            <label for="no_kantin">No Kantin:</label>
            <input type="text" name="no_kantin" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <!-- <div class="form-group">
            <label for="pemasukan">Pemasukan:</label>
            <input type="text" step="0.01" name="pemasukan" class="form-control" required>
        </div> -->

        <button type="submit" class="btn btn-secondary btn-block">Submit</button>
        <a href="home_penjual.php" class="btn btn-outline-secondary btn-block">Cancel</a>
    </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>