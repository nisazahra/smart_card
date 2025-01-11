<?php
    // Termasuk koneksi ke database
    include 'dbconn.php';
    $con = dbconn(); // Asumsi dbconn() adalah fungsi yang mengembalikan koneksi ke database

    if ($con->connect_error) {
        die("Koneksi gagal: " . $con->connect_error);
    }

    // Pastikan parameter id ada
    if (isset($_GET['id'])) {
        $id_menu = $_GET['id'];

        // Mengambil no_kantin berdasarkan id_menu
        $sql = "SELECT no_kantin FROM tabel_menu WHERE id_menu = ?";
        
        if ($stmt = $con->prepare($sql)) {
            // Binding parameter dan eksekusi query
            $stmt->bind_param("i", $id_menu);
            $stmt->execute();
            $stmt->store_result();
            
            // Jika ditemukan no_kantin
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($no_kantin);
                $stmt->fetch();
                
                // Query untuk menghapus menu
                $delete_sql = "DELETE FROM tabel_menu WHERE id_menu = ?";
                if ($delete_stmt = $con->prepare($delete_sql)) {
                    // Binding parameter untuk menghapus data
                    $delete_stmt->bind_param("i", $id_menu);
                    if ($delete_stmt->execute()) {
                        // Redirect ke halaman menu dengan parameter no_kantin
                        header("Location: menu.php?no=$no_kantin");
                        exit();
                    } else {
                        echo "Error deleting record: " . $con->error;
                    }
                } else {
                    echo "Error preparing delete statement: " . $con->error;
                }
            } else {
                echo "Menu not found.";
            }
        } else {
            echo "Error preparing select statement: " . $con->error;
        }
    } else {
        echo "No ID provided!";
    }

    $con->close();
?>
