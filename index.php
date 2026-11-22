<?php
require __DIR__ . '/koneksi.php';

$result = $conn->query('SELECT id_barang, kategori, nama, gambar, harga_beli, harga_jual, stok FROM data_barang ORDER BY id_barang DESC');
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Barang | Lab 8</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <div>
            <p class="eyebrow">Praktikum 8 · PHP &amp; MySQL</p>
            <h1>Data Barang</h1>
            <p class="muted">Kelola persediaan barang melalui operasi CRUD.</p>
        </div>
        <a class="button primary" href="tambah.php">+ Tambah Barang</a>
    </header>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type'] ?? 'success') ?>" role="status"><?= e($flash['message'] ?? '') ?></div>
    <?php endif; ?>

    <section class="table-card" aria-label="Daftar barang">
        <div class="table-scroll">
            <table>
                <thead>
                <tr>
                    <th>Gambar</th><th>Nama Barang</th><th>Kategori</th>
                    <th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td class="empty" colspan="7">Belum ada data barang.</td></tr>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($row['gambar']): ?>
                                    <img class="product-image" src="gambar/<?= e($row['gambar']) ?>" alt="<?= e($row['nama']) ?>">
                                <?php else: ?>
                                    <span class="image-placeholder" aria-label="Tidak ada gambar">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="product-name"><?= e($row['nama']) ?></td>
                            <td><span class="badge"><?= e($row['kategori']) ?></span></td>
                            <td><?= e(format_rupiah($row['harga_beli'])) ?></td>
                            <td><?= e(format_rupiah($row['harga_jual'])) ?></td>
                            <td><?= e((string) $row['stok']) ?></td>
                            <td class="actions">
                                <a class="button small" href="ubah.php?id=<?= (int) $row['id_barang'] ?>">Ubah</a>
                                <form method="post" action="hapus.php" onsubmit="return confirm('Hapus barang ini?')">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_barang'] ?>">
                                    <button class="button small danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer class="table-footer">Jumlah barang: <strong><?= (int) $result->num_rows ?></strong></footer>
    </section>
</main>
</body>
</html>
