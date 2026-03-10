<?php
// edit.php - Edit Barang (Skema Lengkap + PDO)
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$error = "";

try {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Ralat Database: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = trim($_POST['kode_barang']);
    $nama_barang = trim($_POST['nama_barang']);
    $satuan = $_POST['satuan'];
    $harga_beli = filter_input(INPUT_POST, 'harga_beli', FILTER_VALIDATE_FLOAT);
    $harga_jual = filter_input(INPUT_POST, 'harga_jual', FILTER_VALIDATE_FLOAT);
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $keterangan = trim($_POST['keterangan']);

    // Cek duplikasi kode barang (kecuali barang ini sendiri)
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = ? AND id_barang != ?");
    $stmt_check->execute([$kode_barang, $id]);
    if ($stmt_check->fetchColumn() > 0) {
        $error = "Ralat: Kode Barang '$kode_barang' sudah digunakan oleh barang lain.";
    }

    if (!$error) {
        $new_foto_name = $row['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $foto_name = $_FILES['foto']['name'];
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_size = $_FILES['foto']['size'];
            $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($foto_ext, $allowed)) {
                if ($foto_size <= 2097152) {
                    $new_foto_name = uniqid('IMG-', true) . '.' . $foto_ext;
                    if (move_uploaded_file($foto_tmp, 'uploads/' . $new_foto_name)) {
                        // Hapus foto lama jika ada
                        if ($row['foto'] && file_exists('uploads/' . $row['foto'])) {
                            unlink('uploads/' . $row['foto']);
                        }
                    } else {
                        $error = "Ralat: Gagal mengunggah foto baru.";
                    }
                } else {
                    $error = "Ralat: Ukuran foto maksimal 2MB.";
                }
            } else {
                $error = "Ralat: Format foto hanya diperbolehkan JPG/PNG.";
            }
        }

        if (!$error) {
            try {
                $sql = "UPDATE barang SET kode_barang = ?, nama_barang = ?, satuan = ?, harga_beli = ?, harga_jual = ?, jumlah = ?, tanggal_masuk = ?, keterangan = ?, foto = ? 
                        WHERE id_barang = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kode_barang, $nama_barang, $satuan, $harga_beli, $harga_jual, $jumlah, $tanggal_masuk, $keterangan, $new_foto_name, $id]);
                
                $_SESSION['status'] = "✅ Perubahan pada barang '$nama_barang' berhasil disimpan!";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error = "Ralat Database: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Inventaris Barang Elektronik</span>
        </div>
    </nav>

    <div class="container py-2 mb-5">
        <div class="card shadow border-0 mx-auto" style="max-width: 800px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <a href="index.php" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
                <h5 class="mb-0 fw-bold text-primary">Edit Data Barang</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger shadow-sm mb-4"><?= $error ?></div>
                <?php endif; ?>

                <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" class="form-control shadow-sm" required value="<?= htmlspecialchars($row['kode_barang']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control shadow-sm" required value="<?= htmlspecialchars($row['nama_barang']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan" class="form-select shadow-sm" required>
                                <option value="Pcs" <?= $row['satuan'] == 'Pcs' ? 'selected' : '' ?>>Pcs</option>
                                <option value="Unit" <?= $row['satuan'] == 'Unit' ? 'selected' : '' ?>>Unit</option>
                                <option value="Box" <?= $row['satuan'] == 'Box' ? 'selected' : '' ?>>Box</option>
                                <option value="Set" <?= $row['satuan'] == 'Set' ? 'selected' : '' ?>>Set</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control shadow-sm" min="1" required value="<?= $row['jumlah'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Masuk <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" class="form-control shadow-sm" value="<?= $row['tanggal_masuk'] ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="harga_beli" class="form-control" required value="<?= $row['harga_beli'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="harga_jual" class="form-control" required value="<?= $row['harga_jual'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control shadow-sm" rows="3"><?= htmlspecialchars($row['keterangan']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Ganti Foto Barang (Maks. 2MB, JPG/PNG)</label>
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Foto Saat Ini:</p>
                            <?php if ($row['foto'] && file_exists('uploads/' . $row['foto'])): ?>
                                <img src="uploads/<?= $row['foto'] ?>" class="img-thumbnail shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow-sm" style="width: 120px; height: 120px;">
                                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="foto" class="form-control shadow-sm" accept="image/jpeg, image/png">
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> Biarkan kosong jika tidak ingin mengganti foto.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                        <a href="index.php" class="btn btn-light px-4 border">Batal</a>
                        <button type="submit" class="btn btn-warning px-5 shadow-sm fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
