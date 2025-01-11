<?php 
    include 'dbconn.php'; 
    $con = dbconn();

    if ($con->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $no_kantin = $_GET['no']; // Ambil ID siswa dari URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

</head>
<body>
    <a href="home_penjual.php" class="btn btn-outline-primary"><--</a>
    <div class="container text-center mt-5">
        <h1 class="mb-4">Daftar Menu</h1>
        <p>No Kantin: <?php echo htmlspecialchars($no_kantin); ?></p>
    </div>
    <div class="container-fluid my-4">
        <form class="form-inline d-flex align-items-center" method="GET" action="menu.php">
            <div class="input-group w-100 me-2">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Cari berdasarkan nama menu" aria-label="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
                <a class="btn btn-primary" href="insert_menu.php?no=<?php echo $no_kantin;?>" role="button" style="margin-left: 10px;">+</a>
            </div>
        </form>
        <br>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th>Nama Menu</th>
                    <th>Harga Menu</th>
                    <th>Action</th> 
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $sql= "SELECT * FROM tabel_menu WHERE no_kantin=?";

                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = $con->real_escape_string($_GET['search']);
                    $sql .= " AND nama_menu LIKE '%$search%'"; // Gunakan AND karena query sudah memiliki WHERE
                }

                // Menyiapkan statement
                if ($stmt = $con->prepare($sql)) {
                    // Binding parameter
                    $stmt->bind_param("s", $no_kantin);                    
                    // Eksekusi statement
                    $stmt->execute();                    
                    // Mendapatkan hasil
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        // Menampilkan hasil
                        while ($row = $result->fetch_assoc()){
                            echo "<tr>
                                    <td>{$row['nama_menu']}</td>
                                    <td>{$row['harga']}</td>
                                    <td>
                                        <a href='edit_menu.php?id={$row['id_menu']}'>Edit Menu</a> |
                                        <a href='delete_menu.php?id={$row['id_menu']}'
                                        onclick='return confirm(\"Are you sure you want to delete this menu?\")'>Delete Menu</a>
                                        
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data found</td></tr>";
                    }
                } else {
                    echo "Error in query preparation.";
                }
                ?>
            </tbody>

        </table>

    </div>

    <script>
        $(document).ready(function() {
            <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
                $('#successModal').modal('show');
            <?php endif; ?>
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html> 