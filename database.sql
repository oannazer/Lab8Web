CREATE DATABASE IF NOT EXISTS latihan1
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE latihan1;

CREATE TABLE IF NOT EXISTS data_barang (
  id_barang INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  kategori VARCHAR(30) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  gambar VARCHAR(255) DEFAULT NULL,
  harga_beli DECIMAL(12, 0) NOT NULL DEFAULT 0,
  harga_jual DECIMAL(12, 0) NOT NULL DEFAULT 0,
  stok INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO data_barang (kategori, nama, gambar, harga_beli, harga_jual, stok)
SELECT 'Elektronik', 'HP Samsung Android', NULL, 2000000, 2400000, 5
WHERE NOT EXISTS (SELECT 1 FROM data_barang WHERE nama = 'HP Samsung Android');

INSERT INTO data_barang (kategori, nama, gambar, harga_beli, harga_jual, stok)
SELECT 'Elektronik', 'HP Xiaomi Android', NULL, 1000000, 1400000, 5
WHERE NOT EXISTS (SELECT 1 FROM data_barang WHERE nama = 'HP Xiaomi Android');

INSERT INTO data_barang (kategori, nama, gambar, harga_beli, harga_jual, stok)
SELECT 'Elektronik', 'HP OPPO Android', NULL, 1800000, 2300000, 5
WHERE NOT EXISTS (SELECT 1 FROM data_barang WHERE nama = 'HP OPPO Android');
