# Langkah Praktikum 8 - PHP dan Database MySQL

**Nama:** Oan Najmi Zertho  
**NIM/Kelas:** 321510027 / I.25.3A  
**Prodi:** Teknik Informatika  
**Mata Kuliah:** Pemrograman Web  
**Dosen:** Agung Nugroho S.Kom, M.Kom

## Langkah Praktikum - Aplikasi CRUD Data Barang

Praktikum ini menggunakan PHP dan MySQL untuk mengelola data barang. Aplikasi menerapkan empat operasi CRUD: menampilkan (Read), menambah (Create), mengubah (Update), dan menghapus (Delete) data. Data disimpan pada database `latihan1`, sedangkan gambar barang disimpan di folder `gambar/`.

## Persiapan dan Struktur File

Pastikan Apache dan MySQL berjalan pada XAMPP. Salin folder `Lab8Web` ke `C:\xampp\htdocs\lab8_php_database`.

```text
Lab8Web/
├── database.sql       # Membuat database, tabel, dan data awal
├── koneksi.php        # Koneksi database dan fungsi pendukung
├── index.php          # Menampilkan daftar barang (Read)
├── tambah.php         # Menambahkan barang (Create)
├── ubah.php           # Mengubah barang (Update)
├── hapus.php          # Menghapus barang (Delete)
├── style.css          # Tampilan responsif
├── gambar/            # Gambar barang yang diunggah
└── screenshots/       # Bukti tampilan praktikum
```

## Menyiapkan Database MySQL

Buka `http://localhost/phpmyadmin/`, pilih tab **SQL**, lalu jalankan isi file `database.sql`. Script membuat database `latihan1`, tabel `data_barang`, dan tiga contoh data elektronik.

```sql
CREATE DATABASE IF NOT EXISTS latihan1;
USE latihan1;

CREATE TABLE IF NOT EXISTS data_barang (
  id_barang INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kategori VARCHAR(30) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  gambar VARCHAR(255) DEFAULT NULL,
  harga_beli DECIMAL(12, 0) NOT NULL DEFAULT 0,
  harga_jual DECIMAL(12, 0) NOT NULL DEFAULT 0,
  stok INT UNSIGNED NOT NULL DEFAULT 0
);
```

- `id_barang` menjadi primary key dan bertambah otomatis untuk setiap baris baru.
- `DECIMAL(12, 0)` menyimpan harga sebagai bilangan bulat dengan kapasitas hingga 12 digit.
- `gambar` menyimpan nama file gambar yang diunggah, bukan data gambar itu sendiri.
- `IF NOT EXISTS` mencegah pembuatan ulang database atau tabel yang sudah ada.

**Screenshot:** simpan bukti database dan tabel sebagai `screenshots/01-database.png`.

## Membuat Koneksi Database

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'latihan1';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');
```

- `$host`, `$user`, `$pass`, dan `$db` berisi pengaturan MySQL lokal XAMPP. Jika konfigurasi MySQL berbeda, ubah bagian ini.
- `new mysqli(...)` membuka koneksi dari PHP ke database.
- `set_charset('utf8mb4')` menetapkan encoding agar karakter teks tersimpan dan terbaca dengan benar.
- `mysqli_report(...)` dan blok `try/catch` di `koneksi.php` menangani kegagalan koneksi tanpa menampilkan detail internal server.

## Menampilkan Data Barang (Read)

```php
$result = $conn->query(
    'SELECT id_barang, kategori, nama, gambar, harga_beli, harga_jual, stok
     FROM data_barang ORDER BY id_barang DESC'
);
```

```php
<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= e($row['nama']) ?></td>
        <td><?= e($row['kategori']) ?></td>
        <td><?= e(format_rupiah($row['harga_jual'])) ?></td>
        <td><?= (int) $row['stok'] ?></td>
    </tr>
<?php endwhile; ?>
```

- Query `SELECT` mengambil seluruh kolom yang dibutuhkan tabel daftar.
- `ORDER BY id_barang DESC` menampilkan barang terbaru lebih dulu.
- `fetch_assoc()` membaca tiap baris sebagai array asosiatif; perulangan membuat satu baris tabel untuk tiap barang.
- `e()` meng-escape teks dengan `htmlspecialchars()` sebelum ditampilkan.
- `format_rupiah()` menambahkan format Rupiah pada nilai harga.
- Jika belum ada baris, aplikasi menampilkan pesan bahwa data masih kosong.

**Screenshot:** simpan bukti daftar barang sebagai `screenshots/02-read-data.png`.

## Menambah Data (Create)

```php
$stmt = $conn->prepare(
    'INSERT INTO data_barang (nama, kategori, harga_beli, harga_jual, stok, gambar)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssss', $nama, $kategori, $hargaBeli, $hargaJual, $stok, $gambar);
$stmt->execute();
```

- `prepare()` membuat query dengan placeholder `?` untuk nilai masukan.
- `bind_param()` mengikat nilai ke placeholder; cara ini menghindari penyusunan SQL langsung dari teks pengguna.
- `execute()` menjalankan perintah dan menyimpan baris baru.
- `tambah.php` juga memvalidasi nama, kategori, harga, stok, dan file gambar sebelum menyimpan.
- Form menggunakan `enctype="multipart/form-data"` agar berkas dapat dikirim.
- Gambar opsional dibatasi pada JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.

**Screenshot:** simpan form tambah barang sebagai `screenshots/03-create-form.png`.

**Screenshot:** simpan hasil barang yang ditambahkan sebagai `screenshots/04-create-result.png`.

## Mengubah Data (Update)

```php
$stmt = $conn->prepare(
    'UPDATE data_barang
     SET nama = ?, kategori = ?, harga_beli = ?, harga_jual = ?, stok = ?, gambar = ?
     WHERE id_barang = ?'
);
$stmt->bind_param('ssssssi', $nama, $kategori, $hargaBeli, $hargaJual, $stok, $gambar, $id);
$stmt->execute();
```

- ID diambil dari parameter URL lalu divalidasi sebagai integer.
- Query awal `SELECT ... WHERE id_barang = ?` mengambil data lama untuk mengisi nilai form.
- Query `UPDATE` memakai prepared statement dan membatasi perubahan dengan `WHERE id_barang = ?`.
- Jika gambar baru tidak dipilih, nama file lama dipertahankan.
- Setelah perubahan tersimpan, pengguna diarahkan kembali ke daftar dengan pesan hasil.

**Screenshot:** simpan form edit barang sebagai `screenshots/05-update-form.png`.

**Screenshot:** simpan hasil perubahan data sebagai `screenshots/06-update-result.png`.

## Menghapus Data (Delete)

```php
$stmt = $conn->prepare('DELETE FROM data_barang WHERE id_barang = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
```

- ID barang harus bilangan bulat positif.
- Penghapusan hanya diterima melalui metode POST dan meminta konfirmasi di browser.
- Token CSRF diperiksa sebelum data dihapus.
- Setelah baris dihapus, gambar yang terkait juga dibersihkan dari folder `gambar/`.
- Jika data tidak ditemukan, aplikasi menampilkan pesan dan kembali ke daftar.

**Screenshot:** simpan konfirmasi sebelum penghapusan sebagai `screenshots/07-delete-confirm.png`.

**Screenshot:** simpan daftar setelah penghapusan sebagai `screenshots/08-delete-result.png`.

## Pengamanan dan Validasi

- Nilai teks di-escape menggunakan `htmlspecialchars()` melalui fungsi `e()`.
- Query tambah, ubah, dan hapus menggunakan prepared statements.
- Form perubahan data memakai token CSRF untuk memastikan permintaan berasal dari sesi aplikasi.
- File gambar divalidasi berdasarkan MIME type, ekstensi yang diizinkan, dan batas ukuran.
- Nama berkas yang disimpan dibuat acak untuk menghindari benturan nama serta penggunaan nama file yang tidak aman.
- Validasi HTML memberi panduan awal, sedangkan PHP tetap memeriksa input di server.

## Menjalankan Aplikasi

1. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
2. Impor `database.sql` melalui phpMyAdmin.
3. Letakkan isi folder proyek pada `C:\xampp\htdocs\lab8_php_database`.
4. Buka `http://localhost/lab8_php_database/`.
5. Tambahkan, ubah, dan hapus data untuk mencoba alur CRUD.

## Ringkasan Hasil

Aplikasi menyimpan data barang dalam MySQL dan menyediakan daftar, form tambah, form ubah, serta aksi hapus. Penggunaan query terparameterisasi, validasi input, dan escaping output membantu menjaga pemrosesan data tetap aman.

## Bukti Screenshot

Folder `screenshots/` disiapkan untuk bukti database dan setiap operasi CRUD. Screenshot belum tersedia; ambil dari browser setelah Apache dan MySQL dijalankan pada XAMPP, lalu gunakan nama file sesuai bagian di atas.
