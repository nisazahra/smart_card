<?php
include 'dbconn.php';
$con = dbconn();

if (isset($_GET['no'])) {
    $no = $_GET['no'];

    $sql = "DELETE FROM tabel_transaksi WHERE no_transaksi = $no";
    if ($con->query($sql) === TRUE) {
        header("Location: home.php");
    } else {
        echo "Error deleting record: " . $con->error;
    }
}
?>
