<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Kartu RFID</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            setInterval(function() {
                $.get("UID_container.php", function(uid) {
                    const uid_kartu = uid.trim();
                    console.log("UID Data:", uid_kartu); // Debugging

                    if (uid_kartu) {
                        $("#uid_kartu").val(uid_kartu); // Isi input UID dengan data
                        
                        // Kirim UID ke server
                        $.ajax({
                            url: "get_data_siswa.php",
                            type: "POST",
                            data: { uid_kartu: uid_kartu },
                            success: function(response) {
                                console.log("Server Response:", response); // Debugging
                                try {
                                    const result = JSON.parse(response);
                                    if (result.status === "success") {
                                        const data = result.data;
                                        $("#id_siswa").val(data.id_siswa);
                                        $("#nama").val(data.nama);
                                        $("#saldo").val(data.saldo);
                                    } else {
                                        alert(result.message);
                                        $("#id_siswa, #nama, #saldo").val(""); // Kosongkan field jika UID tidak ditemukan
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", xhr.responseText, status, error);
                            }
                        });
                    }
                });
            }, 500); // Update setiap 500ms
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Read Kartu RFID</h1>
        <form id="transaksiForm" class="p-4 border rounded shadow-sm bg-light">
            <div class="form-group">
                <label for="id_siswa">ID Siswa:</label>
                <input type="text" name="id_siswa" id="id_siswa" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="uid_kartu">UID Kartu:</label>
                <input type="text" name="uid_kartu" id="uid_kartu" class="form-control" placeholder="Please scan your card..." readonly>
            </div>
            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" name="nama" id="nama" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="saldo">Saldo:</label>
                <input type="number" name="saldo" id="saldo" class="form-control" readonly>
            </div>
            <a href="home.php" class="btn btn-outline-secondary btn-block">Back</a>
        </form>
    </div>
</body>
</html>
