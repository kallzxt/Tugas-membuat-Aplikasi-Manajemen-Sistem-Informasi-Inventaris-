<?php
session_start();
require_once 'config.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_barang';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

try {
    $sql = "SELECT * FROM barang WHERE 
            nama_barang LIKE :search OR 
            kode_barang LIKE :search 
            ORDER BY $sort $order 
            LIMIT :start, :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    $count_sql = "SELECT COUNT(*) FROM barang WHERE 
                  nama_barang LIKE :search OR 
                  kode_barang LIKE :search";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $count_stmt->execute();
    $total_data = $count_stmt->fetchColumn();
    $total_pages = ceil($total_data / $limit);
} catch (PDOException $e) {
    die("Kesalahan Query: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventaris - Sistem Stabil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .table img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .badge-info { background-color: #0dcaf0; color: #000; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Inventaris Barang Elektronik</span>
        </div>
    </nav>

    <div class="container py-2">
        <?php if (isset($_SESSION['status'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <?= $_SESSION['status']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['status']); ?>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-primary">Daftar Inventaris</h5>
                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" name="search" placeholder="Cari Kode atau Nama Barang..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white">Urutkan</span>
                            <select class="form-select" name="sort" onchange="this.form.submit()">
                                <option value="id_barang" <?= $sort == 'id_barang' ? 'selected' : '' ?>>Terbaru</option>
                                <option value="nama_barang" <?= $sort == 'nama_barang' ? 'selected' : '' ?>>Nama Barang</option>
                                <option value="jumlah" <?= $sort == 'jumlah' ? 'selected' : '' ?>>Stok Terbanyak</option>
                                <option value="harga_jual" <?= $sort == 'harga_jual' ? 'selected' : '' ?>>Harga Jual</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead class="bg-light">
                            <tr>
                                <th>Foto</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_data > 0): ?>
                                <?php while($row = $stmt->fetch()): ?>
                                    <tr>
                                        <td>
                                            <?php if ($row['foto'] && file_exists('uploads/' . $row['foto'])): ?>
                                                <img src="uploads/<?= $row['foto'] ?>" class="shadow-sm">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow-sm" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-info shadow-sm"><?= $row['kode_barang'] ?></span></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                        <td><?= $row['satuan'] ?></td>
                                        <td>Rp <?= number_format($row['harga_jual'], 2, ',', '.') ?></td>
                                        <td><?= $row['jumlah'] ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm shadow-sm">
                                                <a href="view.php?id=<?= $row['id_barang'] ?>" class="btn btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $row['id_barang'] ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <a href="hapus.php?id=<?= $row['id_barang'] ?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Tidak ada data inventaris ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center pagination-sm">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>&sort=<?= $sort ?>">Sebelumnya</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&sort=<?= $sort ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>&sort=<?= $sort ?>">Selanjutnya</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
