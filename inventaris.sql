-- ==========================================================
-- CURA-LOG  |  Database: inventaris
-- ==========================================================
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `inventaris`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventaris`;

-- ----------------------------------------------------------
-- ruangan
-- ----------------------------------------------------------
CREATE TABLE `ruangan` (
  `id_ruangan`   int(11) NOT NULL AUTO_INCREMENT,
  `nama_ruangan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ruangan` VALUES
(1,'ICU'),(2,'UGD'),(3,'Laboratorium'),
(4,'Radiologi'),(5,'Ruang Rawat Inap'),
(6,'Ruang Operasi'),(7,'Poliklinik');

-- ----------------------------------------------------------
-- alat
-- ----------------------------------------------------------
CREATE TABLE `alat` (
  `id_alat`        int(11) NOT NULL AUTO_INCREMENT,
  `no_seri`        varchar(60)  DEFAULT NULL,
  `nama_alat`      varchar(100) NOT NULL,
  `merk`           varchar(100) DEFAULT NULL,
  `kategori`       varchar(100) DEFAULT NULL,
  `gambar`         varchar(200) DEFAULT NULL,
  `tgl_masuk`      date         DEFAULT NULL,
  `id_ruangan`     int(11)      DEFAULT NULL,
  `masa_kalibrasi` date         DEFAULT NULL,
  `status`         enum('Tersedia','Dipinjam','Rusak','Maintenance','Perlu Kalibrasi')
                                NOT NULL DEFAULT 'Tersedia',
  `kondisi`        enum('Baik','Perlu Kalibrasi','Rusak')
                                NOT NULL DEFAULT 'Baik',
  `keterangan`     text         DEFAULT NULL,
  `created_at`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alat`),
  KEY `fk_alat_ruangan` (`id_ruangan`),
  CONSTRAINT `fk_alat_ruangan`
    FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `alat`
  (`id_alat`,`no_seri`,`nama_alat`,`merk`,`kategori`,`gambar`,
   `tgl_masuk`,`id_ruangan`,`masa_kalibrasi`,`status`,`kondisi`,`keterangan`)
VALUES
(1, 'SN-202401-001','Ventilator',        'Philips',      'Alat Pernapasan',   'ventilator.webp',        '2024-01-10',1,'2025-01-10','Tersedia',       'Baik',NULL),
(2, 'SN-202312-002','Defibrillator',     'Zoll',         'Alat Resusitasi',   'defibrilator.webp',      '2023-12-15',1,'2024-12-15','Tersedia',       'Baik',NULL),
(3, 'SN-202402-003','Infuse Pump',       'B Braun',      'Alat Terapi',       'infuse_pump.webp',       '2024-02-01',2,'2025-02-01','Tersedia',       'Baik',NULL),
(4, 'SN-202311-004','USG',               'GE Healthcare','Alat Diagnostik',   'USG.webp',               '2023-11-20',4,'2024-11-20','Perlu Kalibrasi','Perlu Kalibrasi',NULL),
(5, 'SN-202401-005','EKG',               'Nihon Kohden', 'Alat Diagnostik',   'EKG.webp',               '2024-01-05',2,'2025-01-05','Tersedia',       'Baik',NULL),
(6, 'SN-202310-006','X-Ray',             'Siemens',      'Alat Diagnostik',   'X-ray.webp',             '2023-10-01',4,'2024-10-01','Perlu Kalibrasi','Perlu Kalibrasi',NULL),
(7, 'SN-202403-007','Tensimeter Digital','Omron',        'Alat Monitor',      'Tensimeter_digital.webp','2024-03-01',3,'2025-03-01','Tersedia',       'Baik',NULL),
(8, 'SN-202309-008','Mikroskop',         'Olympus',      'Alat Laboratorium', 'mikroskop.webp',          '2023-09-12',3,'2024-09-12','Tersedia',       'Baik',NULL),
(9, 'SN-202308-009','Autoclave',         'Getinge',      'Alat Sterilisasi',  'autoclave.webp',          '2023-08-20',3,'2024-08-20','Perlu Kalibrasi','Perlu Kalibrasi',NULL),
(10,'SN-202401-010','Suction Pump',      'Laerdal',      'Alat Pernapasan',   'suction_pump.webp',       '2024-01-25',1,'2025-01-25','Tersedia',       'Baik',NULL),
(11,'SN-202402-011','Nebulizer',         'Philips',      'Alat Pernapasan',   'nebulizer.webp',          '2024-02-10',2,'2025-02-10','Tersedia',       'Baik',NULL),
(12,'SN-202401-012','Stetoskop',         'Littmann',     'Alat Diagnostik',   'stethoscope.webp',        '2024-01-15',2,'2025-01-15','Tersedia',       'Baik',NULL),
(13,'SN-202312-013','Syringe Pump',      'Terumo',       'Alat Terapi',       'syringe_pump.webp',       '2023-12-05',1,'2024-12-05','Rusak',          'Rusak','Sedang perbaikan vendor'),
(14,'SN-202403-014','Syringe Filter',    'Millipore',    'Alat Laboratorium', 'syringe_filter.webp',     '2024-03-05',3,'2025-03-05','Tersedia',       'Baik',NULL);

-- ----------------------------------------------------------
-- users
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id`           int(11) NOT NULL AUTO_INCREMENT,
  `username`     varchar(50)  NOT NULL UNIQUE,
  `password`     varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role`         enum('admin','dokter','organizer') NOT NULL DEFAULT 'dokter',
  `created_at`   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- admin/admin123 | 200101-200105/dokter123 | organizer/org123
INSERT INTO `users` (`username`,`password`,`nama_lengkap`,`role`) VALUES
('admin',     MD5('admin123'), 'Administrator RSUD',  'admin'),
('200101',    MD5('dokter123'),'Dr. Budi Santoso',    'dokter'),
('200102',    MD5('dokter123'),'Dr. Siti Rahayu',     'dokter'),
('200103',    MD5('dokter123'),'Dr. Ahmad Fauzi',     'dokter'),
('200104',    MD5('dokter123'),'Dr. Maya Putri',      'dokter'),
('200105',    MD5('dokter123'),'Dr. Rizky Pratama',   'dokter'),
('organizer', MD5('org123'),  'Staff Inventaris',    'organizer');

-- ----------------------------------------------------------
-- history_peminjaman
-- ----------------------------------------------------------
CREATE TABLE `history_peminjaman` (
  `id_history`        int(11) NOT NULL AUTO_INCREMENT,
  `id_alat`           int(11) NOT NULL,
  `id_user`           int(11) NOT NULL,
  `tgl_pinjam`        datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tgl_kembali`       datetime DEFAULT NULL,
  `keperluan`         varchar(255) DEFAULT NULL,
  `ruangan_tujuan`    varchar(100) DEFAULT NULL,
  `status_peminjaman` enum('Dipinjam','Dikembalikan') NOT NULL DEFAULT 'Dipinjam',
  `catatan`           text DEFAULT NULL,
  PRIMARY KEY (`id_history`),
  KEY `fk_hist_alat` (`id_alat`),
  KEY `fk_hist_user` (`id_user`),
  CONSTRAINT `fk_hist_alat` FOREIGN KEY (`id_alat`) REFERENCES `alat`(`id_alat`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- vendor
-- ----------------------------------------------------------
CREATE TABLE `vendor` (
  `id_vendor`   int(11) NOT NULL AUTO_INCREMENT,
  `nama_vendor` varchar(100) NOT NULL,
  `kontak`      varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_vendor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `vendor` VALUES
(1,'PT Kalibrasi Medika','08123456789'),
(2,'CV Teknik Sehat','08234567890'),
(3,'PT Alat Kesehatan Indo','08345678901'),
(4,'CV Medika Service','08456789012');

-- ----------------------------------------------------------
-- standar_kalibrasi
-- ----------------------------------------------------------
CREATE TABLE `standar_kalibrasi` (
  `id_kalibrasi`   int(11) NOT NULL AUTO_INCREMENT,
  `nama_parameter` varchar(100) DEFAULT NULL,
  `nilai_standar`  varchar(50)  DEFAULT NULL,
  PRIMARY KEY (`id_kalibrasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `standar_kalibrasi` VALUES
(1,'Tekanan','120 mmHg'),(2,'Suhu','36-37 C'),
(3,'Volume','500 ml'),(4,'Frekuensi','50 Hz'),
(5,'Arus Listrik','220 V');

COMMIT;
