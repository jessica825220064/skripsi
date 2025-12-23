CREATE DATABASE IF NOT EXISTS dashboard_datamart;
USE dashboard_datamart;

-- Tabel Dimensi Karyawan
CREATE TABLE `dim_karyawan` (
  `sk_karyawan` int(11) NOT NULL AUTO_INCREMENT,
  `karyawanPIN` int(11) NOT NULL,
  PRIMARY KEY (`sk_karyawan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Dimensi Shift
CREATE TABLE `dim_shift` (
  `sk_shift` int(11) NOT NULL AUTO_INCREMENT,
  `NoShift` int(11) NOT NULL,
  `jamMasuk` time DEFAULT NULL,
  `jamPulang` time DEFAULT NULL,
  PRIMARY KEY (`sk_shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Dimensi Waktu
CREATE TABLE `dim_waktu` (
  `sk_waktu` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `bulan` int(11) DEFAULT NULL,
  `nama_bulan` varchar(20) DEFAULT NULL,
  `tahun` int(11) DEFAULT NULL,
  `bulan_tahun` varchar(20) DEFAULT NULL,
  `bulan_tahun_sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`sk_waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Fakta Kinerja 
CREATE TABLE `fact_kinerja` (
  `sk_waktu` int(11) NOT NULL,
  `sk_karyawan` int(11) NOT NULL,
  `sk_shift` int(11) NOT NULL,
  `kehadiran` int(11) DEFAULT 0,
  `tepat_waktu` int(11) DEFAULT 0,
  `terlambat` int(11) DEFAULT 0,
  `produktivitas` int(11) DEFAULT 0,
  `durasi_kerja_menit` int(11) DEFAULT 0,

  INDEX (`sk_waktu`),
  INDEX (`sk_karyawan`),
  INDEX (`sk_shift`),

  CONSTRAINT `fk_fact_waktu` 
    FOREIGN KEY (`sk_waktu`) REFERENCES `dim_waktu` (`sk_waktu`) 
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_fact_karyawan` 
    FOREIGN KEY (`sk_karyawan`) REFERENCES `dim_karyawan` (`sk_karyawan`) 
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_fact_shift` 
    FOREIGN KEY (`sk_shift`) REFERENCES `dim_shift` (`sk_shift`) 
    ON DELETE CASCADE ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;