<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'latihan1';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL berjalan dan database latihan1 sudah dibuat.');
}
mysqli_set_charset($conn, 'utf8mb4');

function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_rupiah($value)
{
    return 'Rp' . number_format((float) $value, 0, ',', '.');
}

function upload_gambar($file)
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gambar gagal diunggah.');
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, atau WebP.');
    }

    $folder = __DIR__ . DIRECTORY_SEPARATOR . 'gambar';
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new RuntimeException('Folder gambar tidak dapat dibuat.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $folder . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }
    return $filename;
}

function hapus_file_gambar($storedName)
{
    $filename = basename((string) $storedName);
    if ($filename === '' || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
        return;
    }
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'gambar' . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

function alamat_gambar($storedName)
{
    $filename = basename((string) $storedName);
    return $filename === '' ? '' : 'gambar/' . rawurlencode($filename);
}
