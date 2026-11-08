-- Kost.io | Modul 2, bagian 3.5 Pemodelan Data
-- MySQL | Jalankan pada database kost_io yang sudah dibuat.
USE `kost_io`;

CREATE TABLE `Akun` (
    `idAkun` INT NOT NULL,
    `namaLengkap` VARCHAR(100),
    `email` VARCHAR(254),
    `noTelepon` VARCHAR(20),
    `kataSandi` VARCHAR(255),
    `jenisAkun` VARCHAR(30),
    PRIMARY KEY (`idAkun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Kost` (
    `idKost` INT NOT NULL,
    `idPemilik` INT NOT NULL,
    `namaKost` VARCHAR(100),
    `alamat` VARCHAR(255),
    `lokasi` VARCHAR(100),
    `tipeKost` VARCHAR(30),
    `fasilitasUmum` VARCHAR(1000),
    `deskripsi` VARCHAR(2000),
    `peraturan` VARCHAR(2000),
    `kontakPemilik` VARCHAR(100),
    `fotoUtama` VARCHAR(255),
    `fotoKost` VARCHAR(255),
    `status` VARCHAR(30),
    `hargaMulai` DECIMAL(15,2),
    PRIMARY KEY (`idKost`),
    FOREIGN KEY (`idPemilik`) REFERENCES `Akun` (`idAkun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Kamar` (
    `idKamar` INT NOT NULL,
    `idKost` INT NOT NULL,
    `nomorKamar` VARCHAR(20),
    `tipeKamar` VARCHAR(50),
    `ukuranKamar` VARCHAR(50),
    `hargaSewa` DECIMAL(15,2),
    `fasilitasKamar` VARCHAR(1000),
    `fotoKamar` VARCHAR(255),
    `statusKamar` VARCHAR(30),
    PRIMARY KEY (`idKamar`),
    FOREIGN KEY (`idKost`) REFERENCES `Kost` (`idKost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `PengajuanSewa` (
    `idPengajuan` INT NOT NULL,
    `idCalonPenghuni` INT NOT NULL,
    `idKamar` INT NOT NULL,
    `idKost` INT NOT NULL,
    `tanggalMulaiSewa` DATE,
    `durasiSewa` INT,
    `jumlahPenghuni` INT,
    `catatan` VARCHAR(1000),
    `berkasKTP` VARCHAR(255),
    `status` VARCHAR(30),
    `alasanPenolakan` VARCHAR(1000),
    `tanggalPengajuan` DATE,
    PRIMARY KEY (`idPengajuan`),
    FOREIGN KEY (`idCalonPenghuni`) REFERENCES `Akun` (`idAkun`),
    FOREIGN KEY (`idKamar`) REFERENCES `Kamar` (`idKamar`),
    FOREIGN KEY (`idKost`) REFERENCES `Kost` (`idKost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Penghuni` (
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
    FOREIGN KEY (`idAkun`) REFERENCES `Akun` (`idAkun`),
    FOREIGN KEY (`idKost`) REFERENCES `Kost` (`idKost`),
    FOREIGN KEY (`idKamar`) REFERENCES `Kamar` (`idKamar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `TagihanSewa` (
    `idTagihan` INT NOT NULL,
    `idPengajuan` INT NOT NULL,
    `idPenghuni` INT,
    `idKamar` INT NOT NULL,
    `periode` VARCHAR(7),
    `jumlah` DECIMAL(15,2),
    `jatuhTempo` DATE,
    `status` VARCHAR(30),
    PRIMARY KEY (`idTagihan`),
    FOREIGN KEY (`idPengajuan`) REFERENCES `PengajuanSewa` (`idPengajuan`),
    FOREIGN KEY (`idPenghuni`) REFERENCES `Penghuni` (`idPenghuni`),
    FOREIGN KEY (`idKamar`) REFERENCES `Kamar` (`idKamar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;