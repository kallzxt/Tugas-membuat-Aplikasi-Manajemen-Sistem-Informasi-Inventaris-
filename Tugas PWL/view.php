<?php
// view.php - Detail Barang (Skema Lengkap + PDO)
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Barang - <?= htmlspecialchars($row['nama_barang']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .detail-img { width: 100%; max-height: 400px; object-fit: contain; background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .info-label { width: 150px; font-weight: 600; color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Inventaris Barang Elektronik</span>
        </div>
    </nav>

    <div class="container py-2 mb-5">
        <div class="card shadow border-0 mx-auto" style="max-width: 900px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <h5 class="mb-0 fw-bold text-primary">Detail Barang Inventaris</h5>
                </div>
                <div>
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil"></i> Edit</a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <?php if ($row['foto'] && file_exists('uploads/' . $row['foto'])): ?>
                            <img src="uploads/<?= $row['foto'] ?>" class="detail-img shadow-sm border">
                        <?php else: ?>
                            <div class="detail-img shadow-sm border d-flex align-items-center justify-content-center text-secondary">
                                <div class="text-center">
                                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                                    <span>Foto Tidak Tersedia</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <div class="p-2">
                            <h3 class="fw-bold mb-3"><?= htmlspecialchars($row['nama_barang']) ?></h3>
                            <div class="d-flex mb-3 align-items-center">
                                <span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm me-2">Kode: <?= $row['kode_barang'] ?></span>
                                <span class="badge bg-secondary px-3 py-2 fs-6 shadow-sm"><?= $row['satuan'] ?></span>
                            </div>
                            <hr class="text-muted opacity-25">
                            
                            <div class="list-group list-group-flush mt-3">
                                <div class="list-group-item bg-transparent border-0 px-0 d-flex">
                                    <div class="info-label">Jumlah Stok</div>
                                    <div class="fw-bold fs-5"><?= $row['jumlah'] ?> <small class="text-muted fw-normal"><?= $row['satuan'] ?></small></div>
                                </div>
                                <div class="list-group-item bg-transparent border-0 px-0 d-flex">
                                    <div class="info-label">Harga Beli</div>
                                    <div class="text-muted">Rp <?= number_format($row['harga_beli'], 2, ',', '.') ?></div>
                                </div>
                                <div class="list-group-item bg-transparent border-0 px-0 d-flex">
                                    <div class="info-label">Harga Jual</div>
                                    <div class="text-success fw-bold fs-5">Rp <?= number_format($row['harga_jual'], 2, ',', '.') ?></div>
                                </div>
                                <div class="list-group-item bg-transparent border-0 px-0 d-flex">
                                    <div class="info-label">Keuntungan</div>
                                    <div class="text-primary fw-semibold">Rp <?= number_format($row['harga_jual'] - $row['harga_beli'], 2, ',', '.') ?></div>
                                </div>
                                <div class="list-group-item bg-transparent border-0 px-0 d-flex">
                                    <div class="info-label">Tanggal Masuk</div>
                                    <div class="text-dark"><?= date('d F Y', strtotime($row['tanggal_masuk'])) ?></div>
                                </div>
                                <div class="list-group-item bg-transparent border-0 px-0 mt-3">
                                    <div class="info-label mb-2">Keterangan:</div>
                                    <div class="p-3 bg-light rounded border-start border-4 border-info">
                                        <?= !empty($row['keterangan']) ? nl2br(htmlspecialchars($row['keterangan'])) : '<span class="text-muted italic">Tidak ada keterangan tambahan.</span>' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
