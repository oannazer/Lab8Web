<?php
require __DIR__ . '/koneksi.php';
$categories = ['Komputer', 'Elektronik', 'Hand Phone'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Data barang tidak ditemukan.'); }

$stmt = $conn->prepare('SELECT id_barang, nama, kategori, harga_beli, harga_jual, stok, gambar FROM data_barang WHERE id_barang = ?');
$stmt->bind_param('i', $id); $stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) { http_response_code(404); exit('Data barang tidak ditemukan.'); }

$errors = [];
$values = $item;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['nama', 'kategori', 'harga_beli', 'harga_jual', 'stok'] as $key) $values[$key] = trim((string) ($_POST[$key] ?? ''));
    if ($values['nama'] === '' || mb_strlen($values['nama']) > 100) $errors[] = 'Nama barang wajib diisi dan maksimal 100 karakter.';
    if (!in_array($values['kategori'], $categories, true)) $errors[] = 'Pilih kategori barang yang tersedia.';
    foreach (['harga_beli', 'harga_jual'] as $field) if (!preg_match('/^\d{1,12}$/', $values[$field])) $errors[] = 'Harga beli dan harga jual harus berupa angka bulat nonnegatif.';
    if (!preg_match('/^\d{1,10}$/', $values['stok'])) $errors[] = 'Stok harus berupa bilangan bulat nonnegatif.';

    if (!$errors) {
        try {
            $newImage = upload_image($_FILES['file_gambar'] ?? null, $item['gambar']);
            $stmt = $conn->prepare('UPDATE data_barang SET nama = ?, kategori = ?, harga_beli = ?, harga_jual = ?, stok = ?, gambar = ? WHERE id_barang = ?');
            $stmt->bind_param('ssssssi', $values['nama'], $values['kategori'], $values['harga_beli'], $values['harga_jual'], $values['stok'], $newImage, $id);
            $stmt->execute();
            if ($newImage !== $item['gambar']) remove_image($item['gambar']);
            redirect_home('Data barang berhasil diperbarui.');
        } catch (Throwable $exception) {
            if (!empty($newImage) && $newImage !== $item['gambar']) remove_image($newImage);
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'Data barang gagal diperbarui.';
        }
    }
}
?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ubah Barang | Lab 8</title><link rel="stylesheet" href="style.css">
</head><body>
<main class="form-container">
    <a class="back-link" href="index.php">← Kembali ke daftar</a>
    <section class="form-card">
        <p class="eyebrow">Data Barang</p><h1>Ubah Barang</h1>
        <p class="muted">Perbarui informasi barang yang dipilih.</p>
        <?php if ($errors): ?><div class="alert error" role="alert"><strong>Data belum dapat diperbarui</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div class="field"><label for="nama">Nama Barang</label><input id="nama" name="nama" maxlength="100" value="<?= e($values['nama']) ?>" required></div>
            <div class="field"><label for="kategori">Kategori</label><select id="kategori" name="kategori"><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= $values['kategori'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
            <div class="grid-two">
                <div class="field"><label for="harga_beli">Harga Beli (Rp)</label><input id="harga_beli" name="harga_beli" type="number" min="0" step="1" value="<?= e((string) $values['harga_beli']) ?>" required></div>
                <div class="field"><label for="harga_jual">Harga Jual (Rp)</label><input id="harga_jual" name="harga_jual" type="number" min="0" step="1" value="<?= e((string) $values['harga_jual']) ?>" required></div>
            </div>
            <div class="field"><label for="stok">Stok</label><input id="stok" name="stok" type="number" min="0" step="1" value="<?= e((string) $values['stok']) ?>" required></div>
            <?php if ($item['gambar']): ?><div class="field current-image"><label>Gambar saat ini</label><img src="gambar/<?= e($item['gambar']) ?>" alt="<?= e($item['nama']) ?>"></div><?php endif; ?>
            <div class="field"><label for="file_gambar">Ganti Gambar (opsional)</label><input id="file_gambar" name="file_gambar" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP · Maksimal 2 MB</small></div>
            <div class="form-actions"><button class="button primary" type="submit">Simpan Perubahan</button><a class="button" href="index.php">Batal</a></div>
        </form>
    </section>
</main>
</body></html>
