<?php 
    include 'dbconn.php'; 
    $con = dbconn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container text-center my-4">
        <h1 class="mb-4">Riwayat Transaksi</h1>
    </div>
    <div class="container-fluid my-4 ">
        <form class="form-inline d-flex align-items-center">
            <div class="input-group w-100 me-2">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Cari berdasarkan ID Siswa atau UID Kartu" aria-label="Search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </div>
        </form>
        <br>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>ID Siswa</th>
                    <th>UID Kartu</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $sql = "SELECT * FROM tabel_siswa";
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = $con->real_escape_string($_GET['search']);
                    $sql .= " WHERE id_siswa LIKE '%$search%' OR uid_kartu LIKE '%$search%'";
                }
                
                $result = $con->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id_siswa']}</td>
                            <td>{$row['uid_kartu']}</td>
                            <td>
                                <a href='riwayat_transaksi.php?id={$row['id_siswa']}'>Info</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>";
                }
                ?>                
            </tbody>
        </table>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>
