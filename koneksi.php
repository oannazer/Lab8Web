<?php
declare(strict_types=1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'latihan1';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa layanan MySQL dan konfigurasi koneksi.');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_rupiah(string|int|float $value): string
{
    return 'Rp' . number_format((float) $value, 0, ',', '.');
}

function redirect_home(string $message, string $type = 'success'): never
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: index.php');
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

function upload_image(?array $file, ?string $oldImage = null): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldImage;
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

    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'gambar';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder gambar tidak dapat dibuat.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }

    return $filename;
}

function remove_image(?string $filename): void
{
    if ($filename && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'gambar' . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
