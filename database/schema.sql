-- Kost.io | Modul 2, bagian 3.5 Pemodelan Data
-- MySQL | Jalankan pada database kost_io yang sudah dibuat.
    `jumlahPenghuni` INT,
    `catatan` VARCHAR(1000),
    `berkasKTP` VARCHAR(255),
    `status` VARCHAR(30),
    `alasanPenolakan` VARCHAR(1000),
    `tanggalPengajuan` DATE,
    PRIMARY KEY (`idPengajuan`),
    FOREIGN KEY (`idCalonPenghuni`) REFERENCES `akun` (`idAkun`),
    FOREIGN KEY (`idKamar`) REFERENCES `kamar` (`idKamar`),
    FOREIGN KEY (`idKost`) REFERENCES `kost` (`idKost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `penghuni` (
    `idPenghuni` INT NOT NULL,
    `idAkun` INT NOT NULL,
    `idKost` INT NOT NULL,
    `idKamar` INT NOT NULL,
    `namaLengkap` VARCHAR(100),
    `noTelepon` VARCHAR(20),
    `tanggalMulaiSewa` DATE,
    `tanggalAkhirSewa` DATE,
    `status` VARCHAR(30),
    PRIMARY KEY (`idPenghuni`),
    FOREIGN KEY (`idAkun`) REFERENCES `akun` (`idAkun`),
    FOREIGN KEY (`idKost`) REFERENCES `kost` (`idKost`),
    FOREIGN KEY (`idKamar`) REFERENCES `kamar` (`idKamar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tagihansewa` (
    `idTagihan` INT NOT NULL,
    `idPengajuan` INT NOT NULL,
    `idPenghuni` INT,
    `idKamar` INT NOT NULL,
    `periode` VARCHAR(7),
    `jumlah` DECIMAL(15,2),
    `jatuhTempo` DATE,
    `status` VARCHAR(30),
    PRIMARY KEY (`idTagihan`),
    FOREIGN KEY (`idPengajuan`) REFERENCES `pengajuansewa` (`idPengajuan`),
    FOREIGN KEY (`idPenghuni`) REFERENCES `penghuni` (`idPenghuni`),
    FOREIGN KEY (`idKamar`) REFERENCES `kamar` (`idKamar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UC-07 / UC-13 | Opsi pembayaran yang disediakan pemilik kost.
CREATE TABLE IF NOT EXISTS `metodepembayaran` (
    `idMetode` INT NOT NULL,
    `idPemilikKost` INT NOT NULL,
    `jenis` VARCHAR(30),
    `kodeQRIS` VARCHAR(255),
    `namaBank` VARCHAR(50),
    `noRekening` VARCHAR(34),
    `atasNama` VARCHAR(100),
    PRIMARY KEY (`idMetode`),
    FOREIGN KEY (`idPemilikKost`) REFERENCES `akun` (`idAkun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UC-07 / UC-13 | Bukti dan verifikasi pembayaran sewa.
CREATE TABLE IF NOT EXISTS `pembayaran` (
    `idPembayaran` INT NOT NULL,
    `idTagihan` INT NOT NULL,
    `idPenghuni` INT NOT NULL,
    `idMetode` INT NOT NULL,
    `metode` VARCHAR(50),
    `jumlahPembayaran` DECIMAL(15,2),
    `buktiBayar` VARCHAR(255),
    `tanggalPembayaran` DATETIME,
    `statusPembayaran` VARCHAR(30),
    `alasanPenolakan` VARCHAR(1000),
    PRIMARY KEY (`idPembayaran`),
    FOREIGN KEY (`idTagihan`) REFERENCES `tagihansewa` (`idTagihan`),
    FOREIGN KEY (`idPenghuni`) REFERENCES `penghuni` (`idPenghuni`),
    FOREIGN KEY (`idMetode`) REFERENCES `metodepembayaran` (`idMetode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UC-15 | Catatan transaksi yang menjadi sumber laporan keuangan.
CREATE TABLE IF NOT EXISTS `datakeuangan` (
    `idTransaksi` INT NOT NULL,
    `idKost` INT NOT NULL,
    `idPembayaran` INT NOT NULL,
    `nominal` DECIMAL(15,2),
    `tanggalTransaksi` DATE,
    PRIMARY KEY (`idTransaksi`),
    FOREIGN KEY (`idKost`) REFERENCES `kost` (`idKost`),
    FOREIGN KEY (`idPembayaran`) REFERENCES `pembayaran` (`idPembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UC-08 / UC-14 | Data keluhan/kerusakan penghuni; dapat ditampilkan pemilik.
CREATE TABLE IF NOT EXISTS `keluhan` (
    `idKeluhan` INT NOT NULL AUTO_INCREMENT,
    `idKost` INT NOT NULL,
    `idPenghuni` INT NOT NULL,
    `deskripsi` VARCHAR(2000) NOT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Diajukan',
    `tanggalPengajuan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `informasiTambahan` VARCHAR(2000) DEFAULT NULL,
    PRIMARY KEY (`idKeluhan`),
    INDEX `idx_keluhan_kost_status` (`idKost`, `status`),
    INDEX `idx_keluhan_penghuni` (`idPenghuni`),
    CONSTRAINT `fk_keluhan_kost` FOREIGN KEY (`idKost`) REFERENCES `kost` (`idKost`),
    CONSTRAINT `fk_keluhan_penghuni` FOREIGN KEY (`idPenghuni`) REFERENCES `penghuni` (`idPenghuni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UC-06 / UC-08 / UC-13 / UC-14 | Notifikasi untuk penghuni atau pemilik.
CREATE TABLE IF NOT EXISTS `notifikasi` (
    `idNotifikasi` INT NOT NULL,
    `idPemilikKost` INT DEFAULT NULL,
    `idPenghuni` INT DEFAULT NULL,
    `idPengajuan` INT DEFAULT NULL,
    `idPembayaran` INT DEFAULT NULL,
    `pesan` VARCHAR(2000),
    `waktuKirim` DATETIME,
    `status` VARCHAR(20),
    PRIMARY KEY (`idNotifikasi`),
    FOREIGN KEY (`idPemilikKost`) REFERENCES `akun` (`idAkun`),
    FOREIGN KEY (`idPenghuni`) REFERENCES `penghuni` (`idPenghuni`),
    FOREIGN KEY (`idPengajuan`) REFERENCES `pengajuansewa` (`idPengajuan`),
    FOREIGN KEY (`idPembayaran`) REFERENCES `pembayaran` (`idPembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
