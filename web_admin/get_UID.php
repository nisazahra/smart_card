<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $UIDresult = $_POST["uid_kartu"];
        $Write = "<?php $" . "UIDresult='" . $UIDresult . "'; " . "echo $" . "UIDresult;" . " ?>";
        file_put_contents('UID_container.php', $Write);

        echo "UID berhasil diterima: " . $UIDresult; // Respon balik ke NodeMCU
    } else {
        echo "Metode tidak valid!";
    }
?>
