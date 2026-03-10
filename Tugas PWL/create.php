<?php
session_start();
require_once 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = trim($_POST['kode_barang']);
    $nama_barang = trim($_POST['nama_barang']);
    $satuan = $_POST['satuan'];
    $harga_beli = filter_input(INPUT_POST, 'harga_beli', FILTER_VALIDATE_FLOAT);
    $harga_jual = filter_input(INPUT_POST, 'harga_jual', FILTER_VALIDATE_FLOAT);
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $keterangan = trim($_POST['keterangan']);

    
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = ?");
    $stmt_check->execute([$kode_barang]);
    if ($stmt_check->fetchColumn() > 0) {
        $error = "Ralat: Kode Barang '$kode_barang' sudah terdaftar dalam sistem.";
    }

    if (!$error) {
        $new_foto_name = "";
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $foto_name = $_FILES['foto']['name'];
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_size = $_FILES['foto']['size'];
            $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($foto_ext, $allowed)) {
                if ($foto_size <= 2097152) { // 2MB
                    $new_foto_name = uniqid('IMG-', true) . '.' . $foto_ext;
                    if (!move_uploaded_file($foto_tmp, 'uploads/' . $new_foto_name)) {
                        $error = "Ralat: Gagal mengunggah foto ke server.";
                    }
                } else {
                    $error = "Ralat: Ukuran foto maksimal adalah 2MB.";
                }
            } else {
                $error = "Ralat: Format foto hanya diperbolehkan JPG, JPEG, atau PNG.";
            }
        }

        if (!$error) {
            try {
                $sql = "INSERT INTO barang (kode_barang, nama_barang, satuan, harga_beli, harga_jual, jumlah, tanggal_masuk, keterangan, foto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kode_barang, $nama_barang, $satuan, $harga_beli, $harga_jual, $jumlah, $tanggal_masuk, $keterangan, $new_foto_name]);
                
                $_SESSION['status'] = "✅ Data barang '$nama_barang' berhasil ditambahkan!";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error = "Ralat Database: " . $e->getMessage();
                if ($new_foto_name && file_exists('uploads/' . $new_foto_name)) unlink('uploads/' . $new_foto_name);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang - Sistem Inventaris</title>
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
                <h5 class="mb-0 fw-bold text-primary">Tambah Barang Baru</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger shadow-sm mb-4"><?= $error ?></div>
                <?php endif; ?>

                <form action="create.php" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" class="form-control shadow-sm" placeholder="Contoh: LAP-001" required value="<?= isset($_POST['kode_barang']) ? htmlspecialchars($_POST['kode_barang']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control shadow-sm" placeholder="Nama lengkap barang..." required value="<?= isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : '' ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan" class="form-select shadow-sm" required>
                                <option value="Pcs" <?= (isset($_POST['satuan']) && $_POST['satuan'] == 'Pcs') ? 'selected' : '' ?>>Pcs</option>
                                <option value="Unit" <?= (isset($_POST['satuan']) && $_POST['satuan'] == 'Unit') ? 'selected' : '' ?>>Unit</option>
                                <option value="Box" <?= (isset($_POST['satuan']) && $_POST['satuan'] == 'Box') ? 'selected' : '' ?>>Box</option>
                                <option value="Set" <?= (isset($_POST['satuan']) && $_POST['satuan'] == 'Set') ? 'selected' : '' ?>>Set</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control shadow-sm" min="1" required value="<?= isset($_POST['jumlah']) ? htmlspecialchars($_POST['jumlah']) : '1' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Masuk <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" class="form-control shadow-sm" value="<?= isset($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" name="harga_beli" class="form-control border-start-0" required value="<?= isset($_POST['harga_beli']) ? htmlspecialchars($_POST['harga_beli']) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" name="harga_jual" class="form-control border-start-0" required value="<?= isset($_POST['harga_jual']) ? htmlspecialchars($_POST['harga_jual']) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control shadow-sm" rows="3" placeholder="Contoh: Garansi distributor 1 tahun..."><?= isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Foto Barang (Maks. 2MB, JPG/PNG)</label>
                        <input type="file" name="foto" class="form-control shadow-sm" accept="image/jpeg, image/png">
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> Biarkan kosong jika tidak ada foto.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-light px-4">Reset</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">Simpan Data Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
