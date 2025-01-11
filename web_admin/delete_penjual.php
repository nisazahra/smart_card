<?php
include 'dbconn.php';
$con = dbconn();

if (isset($_GET['no'])) {
    $no = $_GET['no'];

    $sql = "DELETE FROM tabel_penjual WHERE no_kantin = $no";
    if ($con->query($sql) === TRUE) {
        header("Location: home_penjual.php");
    } else {
        echo "Error deleting record: " . $con->error;
    }
}
?>
