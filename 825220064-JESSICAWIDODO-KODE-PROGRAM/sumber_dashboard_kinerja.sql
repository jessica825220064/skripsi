CREATE DATABASE IF NOT EXISTS dashboard_kinerja;
USE dashboard_kinerja;

-- 1. Tabel data_shift 
CREATE TABLE `data_shift` (
  `NoShift` int(1) NOT NULL,
  `JamMasuk` varchar(8) DEFAULT NULL,
  `JamKeluar` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`NoShift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. Tabel data_karyawan
CREATE TABLE `data_karyawan` (
  `KaryawanPIN` int(4) NOT NULL,
  `Nama` varchar(16) DEFAULT NULL,
  `Departemen` varchar(14) DEFAULT NULL,
  `Posisi` varchar(10) DEFAULT NULL,
  `TanggalMasuk` varchar(10) DEFAULT NULL,
  `NoShift` int(1) DEFAULT NULL,
  PRIMARY KEY (`KaryawanPIN`),
  CONSTRAINT `FK_karyawan_shift` FOREIGN KEY (`NoShift`) REFERENCES `data_shift` (`NoShift`) 
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 3. Tabel data_absensi
CREATE TABLE `data_absensi` (
  `NoAbsen` int(4) NOT NULL AUTO_INCREMENT,
  `KaryawanPIN` int(4) DEFAULT NULL,
  `Tanggal` varchar(10) DEFAULT NULL,
  `JamMasuk` varchar(8) DEFAULT NULL,
  `JamKeluar` varchar(8) DEFAULT NULL,
  `NoShift` int(1) DEFAULT NULL,
  PRIMARY KEY (`NoAbsen`),
  CONSTRAINT `FK_absensi_karyawan` FOREIGN KEY (`KaryawanPIN`) REFERENCES `data_karyawan` (`KaryawanPIN`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_absensi_shift` FOREIGN KEY (`NoShift`) REFERENCES `data_shift` (`NoShift`)
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 4. Tabel data_kecelakaan_kerja
CREATE TABLE `data_kecelakaan_kerja` (
  `NoKecelakaan` int(3) NOT NULL AUTO_INCREMENT,
  `Tanggal` varchar(10) DEFAULT NULL,
  `JenisInsiden` varchar(9) DEFAULT NULL,
  `KaryawanPIN` int(4) DEFAULT NULL,
  `Shift` int(1) DEFAULT NULL,
  `Penyebab` varchar(24) DEFAULT NULL,
  `TindakanPerbaikan` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`NoKecelakaan`),
  CONSTRAINT `FK_kecelakaan_karyawan` FOREIGN KEY (`KaryawanPIN`) REFERENCES `data_karyawan` (`KaryawanPIN`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_kecelakaan_shift` FOREIGN KEY (`Shift`) REFERENCES `data_shift` (`NoShift`)
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 5. Tabel data_produksi
CREATE TABLE `data_produksi` (
  `NoPO` int(4) NOT NULL,
  `Tanggal` varchar(10) DEFAULT NULL,
  `Type` varchar(5) DEFAULT NULL,
  `Brand` varchar(6) DEFAULT NULL,
  `Size` varchar(5) DEFAULT NULL,
  `ProduksiTarget` int(3) DEFAULT NULL,
  `TargetSatuan` varchar(4) DEFAULT NULL,
  `ProduksiAktual` int(3) DEFAULT NULL,
  `AktualSatuan` varchar(4) DEFAULT NULL,
  `OprPIN` int(4) DEFAULT NULL,
  `NoShift` int(1) DEFAULT NULL,
  `Ext` varchar(9) DEFAULT NULL,
  `SPKNo` varchar(20) DEFAULT NULL,
  `CoilNo` varchar(15) DEFAULT NULL,
  `Panjang` int(3) DEFAULT NULL,
  `PassedQty` int(3) DEFAULT NULL,
  `PassedSatuan` varchar(4) DEFAULT NULL,
  `PassedWarna` varchar(5) DEFAULT NULL,
  `RejectQty` int(1) DEFAULT NULL,
  `RejectReason` varchar(18) DEFAULT NULL,
  `CoilPIN` int(3) DEFAULT NULL,
  PRIMARY KEY (`NoPO`),
  CONSTRAINT `FK_produksi_karyawan` FOREIGN KEY (`OprPIN`) REFERENCES `data_karyawan` (`KaryawanPIN`)
  ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_produksi_shift` FOREIGN KEY (`NoShift`) REFERENCES `data_shift` (`NoShift`)
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;