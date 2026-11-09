-- Kost.io | Data uji UC-12 Mengelola Data Penghuni dan UC-13 Memverifikasi Pembayaran
-- File ini HANYA berisi INSERT dan tidak mengubah schema.sql.
-- Jalankan SEKALI di database kost_io (phpMyAdmin, tab Impor) setelah schema.sql.
-- Id memakai angka 1301 ke atas supaya tidak bentrok dengan data uji lain.
USE `kost_io`;

-- Akun: 1 pemilik, 2 penghuni
INSERT INTO `akun` (`idAkun`, `namaLengkap`, `email`, `noTelepon`, `kataSandi`, `jenisAkun`) VALUES
(1301, 'Pemilik Kost',  'pemilik.uc13@email.com', '081200001301', '-', 'Pemilik Kost'),
(1302, 'Rifa Aqilla',   'rifa.uc13@email.com',    '081200001302', '-', 'Penghuni'),
(1303, 'Budi Santoso',  'budi.uc13@email.com',    '081200001303', '-', 'Penghuni');

INSERT INTO `kost`
    (`idKost`, `idPemilik`, `namaKost`, `alamat`, `lokasi`, `tipeKost`, `fasilitasUmum`, `deskripsi`, `peraturan`, `kontakPemilik`, `fotoUtama`, `fotoKost`, `status`, `hargaMulai`) VALUES
(1301, 1301, 'Kost Cemara', 'Jl. Cemara No. 3, Lowokwaru', 'Lowokwaru, Malang', 'Putri', 'Wi-Fi, Dapur', 'Kost dekat kampus', 'Tamu sampai 21.00', '081200001301', NULL, NULL, 'Dipublikasikan', 850000.00);

INSERT INTO `kamar` (`idKamar`, `idKost`, `nomorKamar`, `tipeKamar`, `ukuranKamar`, `hargaSewa`, `fasilitasKamar`, `fotoKamar`, `statusKamar`) VALUES
(1301, 1301, 'A01', 'Standar', '3x4', 850000.00, 'Kasur, Lemari, Meja', NULL, 'Terisi'),
(1302, 1301, 'A02', 'Standar', '3x4', 850000.00, 'Kasur, Lemari, Meja', NULL, 'Terisi');

INSERT INTO `pengajuansewa` (`idPengajuan`, `idCalonPenghuni`, `idKamar`, `idKost`, `tanggalMulaiSewa`, `durasiSewa`, `jumlahPenghuni`, `catatan`, `berkasKTP`, `status`, `alasanPenolakan`, `tanggalPengajuan`) VALUES
(1301, 1302, 1301, 1301, '2026-11-01', 6, 1, NULL, NULL, 'Disetujui', NULL, '2026-10-20'),
(1302, 1303, 1302, 1301, '2026-10-01', 6, 1, NULL, NULL, 'Disetujui', NULL, '2026-09-20');

INSERT INTO `penghuni` (`idPenghuni`, `idAkun`, `idKost`, `idKamar`, `namaLengkap`, `noTelepon`, `tanggalMulaiSewa`, `tanggalAkhirSewa`, `status`) VALUES
(1301, 1302, 1301, 1301, 'Rifa Aqilla',  '081200001302', '2026-11-01', '2027-04-30', 'Aktif'),
(1302, 1303, 1301, 1302, 'Budi Santoso', '081200001303', '2026-10-01', '2027-03-31', 'Aktif');

-- Tagihan November 2026 sudah dibayar penghuni lewat UC-07 (menunggu verifikasi)
INSERT INTO `tagihansewa` (`idTagihan`, `idPengajuan`, `idPenghuni`, `idKamar`, `periode`, `jumlah`, `jatuhTempo`, `status`) VALUES
(1301, 1301, 1301, 1301, '2026-11', 850000.00, '2026-11-05', 'Menunggu Verifikasi'),
(1302, 1302, 1302, 1302, '2026-11', 850000.00, '2026-11-05', 'Menunggu Verifikasi');

INSERT INTO `metodepembayaran` (`idMetode`, `idPemilikKost`, `jenis`, `kodeQRIS`, `namaBank`, `noRekening`, `atasNama`) VALUES
(1301, 1301, 'Transfer Bank', NULL, 'Mandiri', '1234567890', 'Pemilik Kost Cemara');

INSERT INTO `pembayaran` (`idPembayaran`, `idTagihan`, `idPenghuni`, `idMetode`, `metode`, `jumlahPembayaran`, `buktiBayar`, `tanggalPembayaran`, `statusPembayaran`, `alasanPenolakan`) VALUES
(1301, 1301, 1301, 1301, 'Transfer Bank', 850000.00, 'bukti/contoh-1301.png', '2026-10-30 10:15:00', 'Menunggu Verifikasi', NULL),
(1302, 1302, 1302, 1301, 'Transfer Bank', 850000.00, 'bukti/contoh-1302.png', '2026-10-31 08:40:00', 'Menunggu Verifikasi', NULL);

-- Untuk mencoba halaman: login sebagai pemilik.uc13@email.com, atau isi sesi idAkun = 1301.
-- Gambar bukti contoh: salin database/bukti-contoh/contoh-1301.png dan contoh-1302.png ke storage/app/private/bukti/

-- Untuk menghapus data uji ini (urutan terbalik karena foreign key):
-- DELETE FROM `notifikasi`       WHERE `idPembayaran` IN (1301, 1302);
-- DELETE FROM `datakeuangan`     WHERE `idPembayaran` IN (1301, 1302);
-- DELETE FROM `pembayaran`       WHERE `idPembayaran` IN (1301, 1302);
-- DELETE FROM `metodepembayaran` WHERE `idMetode` = 1301;
-- DELETE FROM `tagihansewa`      WHERE `idTagihan` IN (1301, 1302);
-- DELETE FROM `penghuni`         WHERE `idPenghuni` IN (1301, 1302);
-- DELETE FROM `pengajuansewa`    WHERE `idPengajuan` IN (1301, 1302);
-- DELETE FROM `kamar`            WHERE `idKamar` IN (1301, 1302);
-- DELETE FROM `kost`             WHERE `idKost` = 1301;
-- DELETE FROM `akun`             WHERE `idAkun` BETWEEN 1301 AND 1303;
