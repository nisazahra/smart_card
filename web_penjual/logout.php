<?php
// Memulai session
session_start();

// Menghapus semua data session
session_unset();

// Mengakhiri session
session_destroy();

// Redirect ke halaman login atau halaman lain yang diinginkan
header("Location: login.php");
exit;
?>
