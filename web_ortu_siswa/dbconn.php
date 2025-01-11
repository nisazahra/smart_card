<?php 
function dbconn() {
    $con = mysqli_connect("localhost","root","","smart_canteen");
    return $con;
}
?>
