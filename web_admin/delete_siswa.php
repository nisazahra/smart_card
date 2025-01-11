<?php
include 'dbconn.php';
$con = dbconn();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM tabel_siswa WHERE id_siswa = $id";
    if ($con->query($sql) === TRUE) {
        header("Location: home_siswa.php");
    } else {
        echo "Error deleting record: " . $con->error;
    }
}
?>
