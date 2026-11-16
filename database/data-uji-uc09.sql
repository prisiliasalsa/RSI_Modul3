-- Kost.io | Data uji UC-09 Mengelola dan publikasi data kost
-- File ini HANYA berisi INSERT dan tidak mengubah schema.sql.
-- Jalankan SEKALI di database kost_io (phpMyAdmin, tab Impor) setelah schema.sql.
-- Id memakai angka 9101 ke atas supaya tidak bentrok dengan data uji UC-06 dan akun hasil registrasi.
USE `kost_io`;

-- Satu akun pemilik (9101) dengan tiga kost
INSERT INTO `akun` (`idAkun`, `namaLengkap`, `email`, `noTelepon`, `kataSandi`, `jenisAkun`) VALUES
(9101, 'Pemilik Kost', 'pemilik.kost.uji@email.com', '081200000101', '-', 'Pemilik Kost');

-- Kost Cemara: sudah Dipublikasikan, 3 kamar, belum punya foto (uji unggah foto lewat menu Ubah)
-- Kost Melati: Draft, 0 kamar (uji pesan "Tambahkan minimal satu kamar ...")
-- Kost Mawar : Draft, 2 kamar (uji publikasi berhasil)
INSERT INTO `kost`
    (`idKost`, `idPemilik`, `namaKost`, `alamat`, `lokasi`, `tipeKost`, `fasilitasUmum`, `deskripsi`, `peraturan`, `kontakPemilik`, `fotoUtama`, `fotoKost`, `status`, `hargaMulai`) VALUES
(9101, 9101, 'Kost Cemara', 'Lowokwaru, Malang', 'Lowokwaru, Malang', 'Putra',  'Wi-Fi, Parkir, Dapur', 'Kost dekat kampus',          'Tamu sampai 21.00',          '08123456789', NULL, NULL, 'Dipublikasikan', 1200000.00),
(9102, 9101, 'Kost Melati', 'Sukun, Malang',     'Sukun, Malang',     'Putri',  'Wi-Fi, Laundry',      'Kost nyaman untuk putri',   'Jam malam pukul 22.00',      '08123456789', NULL, NULL, 'Draft',          NULL),
(9103, 9101, 'Kost Mawar',  'Klojen, Malang',    'Klojen, Malang',    'Campur', 'Wi-Fi, AC',           'Kost di tengah kota',       'Dilarang merokok di kamar',  '08123456789', NULL, NULL, 'Draft',          NULL);

INSERT INTO `kamar` (`idKamar`, `idKost`, `nomorKamar`, `tipeKamar`, `hargaSewa`, `statusKamar`) VALUES
(9101, 9101, 'A01', 'Standar', 1200000.00, 'Tersedia'),
(9102, 9101, 'A02', 'Standar', 1200000.00, 'Tersedia'),
(9103, 9101, 'A03', 'Standar', 1200000.00, 'Tersedia'),
(9104, 9103, 'M01', 'Standar', 1100000.00, 'Tersedia'),
(9105, 9103, 'M02', 'Standar', 1100000.00, 'Tersedia');

-- Untuk menghapus data uji ini (urutan harus terbalik karena foreign key):
-- DELETE FROM `kamar` WHERE `idKamar` BETWEEN 9101 AND 9105;
-- DELETE FROM `kost`  WHERE `idKost`  BETWEEN 9101 AND 9103;
-- DELETE FROM `akun`  WHERE `idAkun`  = 9101;
-- Kost yang kamu tambahkan sendiri lewat halaman (id 9104 ke atas atau 1 ke atas) harus dihapus lebih dulu.