<?php
// hapus.php - Hapus Barang (Skema Lengkap + PDO)
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

try {
    // 1. Ambil nama file foto sebelum dihapus dari DB
    $stmt_select = $pdo->prepare("SELECT nama_barang, foto FROM barang WHERE id_barang = ?");
    $stmt_select->execute([$id]);
    $row = $stmt_select->fetch();

    if ($row) {
        $nama_barang = $row['nama_barang'];
        
        // 2. Hapus file foto dari folder fisik jika ada
        if ($row['foto'] && file_exists('uploads/' . $row['foto'])) {
            unlink('uploads/' . $row['foto']);
        }

        // 3. Hapus data dari database
        $stmt_delete = $pdo->prepare("DELETE FROM barang WHERE id_barang = ?");
        $stmt_delete->execute([$id]);

        $_SESSION['status'] = "🗑️ Data barang '$nama_barang' berhasil dihapus dari sistem.";
    } else {
        $_SESSION['status'] = "⚠️ Ralat: Data tidak ditemukan.";
    }
} catch (PDOException $e) {
    $_SESSION['status'] = "❌ Gagal menghapus data: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>
