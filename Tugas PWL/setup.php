<?php
// setup.php - Script Inisialisasi Database Otomatis
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "inventory_db";

try {
    $pdo_init = new PDO("mysql:host=$host", $user, $pass);
    $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>🔧 Memulai Setup Sistem Inventaris...</h2>";

    // 1. Buat Database
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>✅ Database '$db_name' berhasil dibuat/dipastikan ada.</p>";

    // 2. Hubungkan ke DB yang baru dibuat
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. Buat Tabel Barang
    $sql_table = "CREATE TABLE IF NOT EXISTS barang (
        id_barang INT AUTO_INCREMENT PRIMARY KEY,
        kode_barang VARCHAR(50) UNIQUE NOT NULL,
        nama_barang VARCHAR(150) NOT NULL,
        satuan VARCHAR(50),
        harga_beli DECIMAL(15,2),
        harga_jual DECIMAL(15,2),
        jumlah INT,
        tanggal_masuk DATE,
        keterangan TEXT,
        foto VARCHAR(100)
    ) ENGINE=InnoDB";

    $pdo->exec($sql_table);
    echo "<p style='color:green;'>✅ Tabel 'barang' berhasil dibuat/dipastikan ada.</p>";

    // 4. Pastikan folder uploads ada
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
        echo "<p style='color:green;'>✅ Folder 'uploads' berhasil dibuat.</p>";
    }

    echo "<hr>";
    echo "<p><b>Setup Berhasil!</b> Aplikasi siap digunakan.</p>";
    echo "<a href='index.php' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Buka Halaman Utama</a>";

} catch (PDOException $e) {
    die("<p style='color:red;'>❌ Ralat Setup: " . $e->getMessage() . "</p>");
}
?>
