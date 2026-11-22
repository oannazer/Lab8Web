<?php
require __DIR__ . '/koneksi.php';

$errors = [];
$values = ['nama' => '', 'kategori' => 'Elektronik', 'harga_beli' => '', 'harga_jual' => '', 'stok' => ''];
$categories = ['Komputer', 'Elektronik', 'Hand Phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    if ($values['nama'] === '' || mb_strlen($values['nama']) > 100) $errors[] = 'Nama barang wajib diisi dan maksimal 100 karakter.';
    if (!in_array($values['kategori'], $categories, true)) $errors[] = 'Pilih kategori barang yang tersedia.';
    foreach (['harga_beli', 'harga_jual'] as $field) {
        if (!preg_match('/^\d{1,12}$/', $values[$field])) $errors[] = 'Harga beli dan harga jual harus berupa angka bulat nonnegatif.';
    }
    if (!preg_match('/^\d{1,10}$/', $values['stok'])) $errors[] = 'Stok harus berupa bilangan bulat nonnegatif.';

    if (!$errors) {
        try {
            $image = upload_image($_FILES['file_gambar'] ?? null);
            $stmt = $conn->prepare('INSERT INTO data_barang (nama, kategori, harga_beli, harga_jual, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $values['nama'], $values['kategori'], $values['harga_beli'], $values['harga_jual'], $values['stok'], $image);
            $stmt->execute();
            redirect_home('Barang baru berhasil ditambahkan.');
        } catch (Throwable $exception) {
            if (!empty($image)) remove_image($image);
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'Data barang gagal disimpan.';
        }
    }
}
?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Barang | Lab 8</title><link rel="stylesheet" href="style.css">
</head><body>
<main class="form-container">
    <a class="back-link" href="index.php">← Kembali ke daftar</a>
    <section class="form-card">
        <p class="eyebrow">Data Barang</p><h1>Tambah Barang</h1>
        <p class="muted">Lengkapi informasi barang baru.</p>
        <?php if ($errors): ?><div class="alert error" role="alert"><strong>Data belum dapat disimpan</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div class="field"><label for="nama">Nama Barang</label><input id="nama" name="nama" maxlength="100" value="<?= e($values['nama']) ?>" required></div>
            <div class="field"><label for="kategori">Kategori</label><select id="kategori" name="kategori"><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= $values['kategori'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
            <div class="grid-two">
                <div class="field"><label for="harga_beli">Harga Beli (Rp)</label><input id="harga_beli" name="harga_beli" type="number" min="0" step="1" value="<?= e($values['harga_beli']) ?>" required></div>
                <div class="field"><label for="harga_jual">Harga Jual (Rp)</label><input id="harga_jual" name="harga_jual" type="number" min="0" step="1" value="<?= e($values['harga_jual']) ?>" required></div>
            </div>
            <div class="field"><label for="stok">Stok</label><input id="stok" name="stok" type="number" min="0" step="1" value="<?= e($values['stok']) ?>" required></div>
            <div class="field"><label for="file_gambar">Gambar (opsional)</label><input id="file_gambar" name="file_gambar" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP · Maksimal 2 MB</small></div>
            <div class="form-actions"><button class="button primary" type="submit">Simpan Barang</button><a class="button" href="index.php">Batal</a></div>
        </form>
    </section>
</main>
</body></html>
