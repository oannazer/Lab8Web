<?php
require __DIR__ . '/koneksi.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) redirect_home('ID barang tidak valid.', 'error');

$stmt = $conn->prepare('SELECT gambar FROM data_barang WHERE id_barang = ?');
$stmt->bind_param('i', $id); $stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) redirect_home('Barang tidak ditemukan.', 'error');

$stmt = $conn->prepare('DELETE FROM data_barang WHERE id_barang = ?');
$stmt->bind_param('i', $id); $stmt->execute();
remove_image($item['gambar']);
redirect_home('Barang berhasil dihapus.');
