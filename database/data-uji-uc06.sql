-- Kost.io | Data uji UC-06 Melihat pengajuan sewa
-- File ini HANYA berisi INSERT dan tidak mengubah schema.sql.
-- Jalankan SEKALI di database kost_io (phpMyAdmin, tab Impor atau SQL) setelah schema.sql.
-- Id memakai angka 9001 ke atas supaya tidak bentrok dengan akun hasil registrasi.
-- (Registrasi memakai MAX(idAkun)+1, jadi akun baru berikutnya akan bernomor 9004 ke atas.)
USE `kost_io`;

-- Akun: 1 pemilik, 2 calon penghuni (9002 punya pengajuan, 9003 tidak punya)
INSERT INTO `akun` (`idAkun`, `namaLengkap`, `email`, `noTelepon`, `kataSandi`, `jenisAkun`) VALUES
(9001, 'Pemilik Uji',           'pemilik.uji@email.com', '081200000001', '-', 'Pemilik Kost'),
(9002, 'Rifa Aqilla',           'rifa.uji@email.com',    '081200000002', '-', 'Calon Penghuni'),
(9003, 'Calon Tanpa Pengajuan', 'kosong.uji@email.com',  '081200000003', '-', 'Calon Penghuni');

-- Kost Cemara dan Melati
INSERT INTO `kost` (`idKost`, `idPemilik`, `namaKost`, `alamat`, `lokasi`, `tipeKost`, `status`, `hargaMulai`) VALUES
(9001, 9001, 'Cemara', 'Jl. Contoh No. 1, Malang', 'Malang', 'Campur', 'Aktif', 1200000.00),
(9002, 9001, 'Melati', 'Jl. Contoh No. 2, Malang', 'Malang', 'Putri',  'Aktif', 1000000.00);

-- Kamar A01, A02 (Cemara) dan B03 (Melati)
INSERT INTO `kamar` (`idKamar`, `idKost`, `nomorKamar`, `tipeKamar`, `hargaSewa`, `statusKamar`) VALUES
(9001, 9001, 'A01', 'Standar', 1200000.00, 'Tersedia'),
(9002, 9001, 'A02', 'Standar', 1200000.00, 'Tersedia'),
(9003, 9002, 'B03', 'Standar', 1000000.00, 'Tersedia');

-- Tiga pengajuan milik 9002 dengan tiga status (sesuai PC-06)
INSERT INTO `pengajuansewa`
    (`idPengajuan`, `idCalonPenghuni`, `idKamar`, `idKost`, `tanggalMulaiSewa`, `durasiSewa`, `jumlahPenghuni`, `status`, `alasanPenolakan`, `tanggalPengajuan`) VALUES
(9001, 9002, 9001, 9001, '2026-11-01', 6, 1, 'Menunggu Verifikasi', NULL,                        '2026-10-19'),
(9002, 9002, 9003, 9002, '2026-11-01', 6, 1, 'Ditolak',             'Berkas KTP tidak terbaca.', '2026-10-15'),
(9003, 9002, 9002, 9001, '2026-11-01', 6, 1, 'Disetujui',           NULL,                        '2026-10-10');

-- Untuk menghapus data uji ini (urutan harus terbalik karena foreign key):
-- DELETE FROM `pengajuansewa` WHERE `idPengajuan` IN (9001, 9002, 9003);
-- DELETE FROM `kamar`         WHERE `idKamar`     IN (9001, 9002, 9003);
-- DELETE FROM `kost`          WHERE `idKost`      IN (9001, 9002);
-- DELETE FROM `akun`          WHERE `idAkun`      IN (9001, 9002, 9003);