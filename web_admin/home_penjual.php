<?php 
    include 'dbconn.php'; 
    $con = dbconn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PENJUAL</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <a href="home.php" class="btn btn-outline-primary"><--</a>
    <div class="container text-center mt-5">
        <h1 class="mb-4">PENJUAL PAGE</h1>
    </div>
    <div class="container-fluid my-4 ">
        <form class="form-inline d-flex align-items-center">
            <div class="input-group w-100 me-2">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Cari berdasarkan Nomor Kantin" aria-label="Search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
                <a class="btn btn-primary" href="insert_penjual.php" role="button" style="margin-left: 10px;">+</a>
            </div>
        </form>
        <br>
        <table class="table table-striped">
            <thead class="text-center">
                <tr>
                    <th style="width:10%">No Kantin</th>
                    <th style="width:45%">Username</th>
                    <th style="width:45%">Action</th>
                    
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                // $sql = "SELECT * FROM tabel_penjual";
                // if (isset($_GET['search']) && !empty($_GET['search'])) {
                //     $search = $con->real_escape_string($_GET['search']);
                //     $sql .= " WHERE no_kantin LIKE '%$search%'";
                // }

                $sql = "SELECT 
                    k.no_kantin,
                    k.username,
                    COALESCE(SUM(CASE WHEN t.total_transaksi > 0 THEN t.total_transaksi ELSE 0 END), 0) - 
                    COALESCE(SUM(CASE WHEN t.total_transaksi < 0 THEN t.total_transaksi ELSE 0 END), 0) -
                    COALESCE(SUM(p.jumlah_penarikan), 0) AS saldo
                FROM tabel_penjual k
                LEFT JOIN tabel_transaksi t ON k.no_kantin = t.no_kantin
                LEFT JOIN tabel_penarikan p ON k.no_kantin = p.no_kantin";

                
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = $con->real_escape_string($_GET['search']);
                    $sql .= " WHERE k.no_kantin LIKE '%$search%'";
                }

                $sql .= " GROUP BY k.no_kantin, k.username";

                $result = $con->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['no_kantin']}</td>
                            <td>{$row['username']}</td>
                            <td>
                                <a href='info.php?no={$row['no_kantin']}'>Info</a> | 
                                <a href='menu.php?no={$row['no_kantin']}'>Menu</a> | 
                                <a href='update_penjual.php?no={$row['no_kantin']}'>Edit</a> | 
                                <a href='tarik_saldo.php?no={$row['no_kantin']}'>Tarik Saldo</a> |
                                <a href='tracking_transaksi_penjual.php?no={$row['no_kantin']}'>Tracking</a> |
                                <a href='delete_penjual.php?no={$row['no_kantin']}' 
                                onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>";
                }
                ?>                
            </tbody>
        </table>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
        <!-- Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Sukses</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Data berhasil diperbarui!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        // Tampilkan modal jika parameter "success" ada
        $(document).ready(function() {
            <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
                $('#successModal').modal('show');
            <?php endif; ?>
        });
    </script>

</body>
</html>
