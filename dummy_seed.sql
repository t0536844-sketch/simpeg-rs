-- ==============================================
-- SIM Kepegawaian RSUD Mimika - Data Dummy Seed
-- ==============================================
-- Cara pakai:
--   1. CREATE DATABASE rsud_mimika_kepegawaian;
--   2. mysql -u root -p rsud_mimika_kepegawaian < database.sql
--   3. mysql -u root -p rsud_mimika_kepegawaian < dummy_seed.sql
-- ==============================================

INSERT INTO `logs` VALUES
(1,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:22:32'),
(2,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:29:17'),
(3,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:46:19'),
(4,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:49:50'),
(5,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:51:25'),
(6,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 10:54:05'),
(7,1,'LOGOUT',NULL,NULL,'User logout','127.0.0.1','2026-05-19 10:54:05'),
(8,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 11:01:06'),
(9,1,'LOGOUT',NULL,NULL,'User logout','127.0.0.1','2026-05-19 11:01:06'),
(10,1,'LOGIN',NULL,NULL,'User login','127.0.0.1','2026-05-19 11:02:05');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `nama_lengkap` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('Pria','Wanita') DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `pangkat_golongan` varchar(50) DEFAULT NULL,
  `pendidikan` varchar(255) DEFAULT NULL,
  `status_pernikahan` varchar(50) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `status_kepegawaian` varchar(50) DEFAULT NULL,
  `link_sk` text DEFAULT NULL,
  `jumlah_keluarga` int(11) DEFAULT 0,
  `alamat_rumah` text DEFAULT NULL,
  `link_ktp` text DEFAULT NULL,
  `link_kartu_keluarga` text DEFAULT NULL,
  `link_ijazah` text DEFAULT NULL,
  `link_str` text DEFAULT NULL,
  `masa_berlaku_str` date DEFAULT NULL,
  `link_sip` text DEFAULT NULL,
  `masa_berlaku_sip` date DEFAULT NULL,
  `nomor_kartu_pegawai` varchar(100) DEFAULT NULL,
  `link_npwp` text DEFAULT NULL,
  `link_foto` text DEFAULT NULL,
  `link_akta_lahir` text DEFAULT NULL,
  `link_akta_nikah` text DEFAULT NULL,
  `link_skp` text DEFAULT NULL,
  `link_sk_kenaikan_pangkat` text DEFAULT NULL,
  `link_sk_jabatan` text DEFAULT NULL,
  `link_sk_mutasi` text DEFAULT NULL,
  `link_sk_pensiun` text DEFAULT NULL,
  `link_sertifikat` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES
(1,'2026-05-19 10:17:51','Uji coba data','Nabire','1990-01-31','Konghucu','Pria','123123123','IV/a','S1 ilmu kesehatan masyarakat','Menikah','Staf','PNS','https://drive.google.com/open?id=1aYrA86pYxZ9fkAOWtfqCoy6QSGXUGiw-',2,'DINAS','https://drive.google.com/open?id=1TPoRqoVz030dfZYj8MmSvl3SkBvvp99C','https://drive.google.com/open?id=1mVRR9FYy07Hl4CdWZ8Fw10K-naEU4ViP','https://drive.google.com/open?id=1fo2_zcH1Y62RLEKteIzTIplHplbyvftN','https://drive.google.com/open?id=1O3RaQ9J7OsFQP0AOih3bLC48cf2uiRu5','2026-03-04','https://drive.google.com/open?id=147PjKg7uMco3jdTBc1ZgWJwZB7M3i7D6','2026-03-13','123123123','https://drive.google.com/open?id=1G6xGTQch8jON-m8l9TJNaiTXCNwv_JIW','https://drive.google.com/open?id=1KtuNgnxGpTm1XELZ4Q85NSrerKQ8o47G',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 10:17:51','2026-05-19 10:17:51'),
(2,'2026-05-19 11:09:28','Dr. Ahmad Fauzi,Sp.PD','Nabire','1985-03-15','Islam','Pria','198503151985031001','IV/c','S2 Ilmu Penyakit Dalam','Menikah','Dokter Spesialis Penyakit Dalam','PNS',NULL,3,'Jl. Merdeka No. 12, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(3,'2026-05-19 11:09:28','Dr. Siti Nurhaliza,Sp.OG','Jayapura','1990-07-22','Islam','Wanita','199007221990071001','IV/b','S2 Ilmu Kebidanan','Menikah','Dokter Spesialis Obstetri & Ginekologi','PNS',NULL,2,'Jl. Cenderawasih No. 8, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(4,'2026-05-19 11:09:28','Dr. Budi Prasetyo,Sp.B','Merauke','1988-11-05','Kristen','Pria','198811051988111001','IV/a','S2 Ilmu Bedah','Menikah','Dokter Spesialis Bedah Umum','PNS',NULL,1,'Jl. Kartini No. 25, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(5,'2026-05-19 11:09:28','Dr. Maria Ulfah,Sp.A','Biak','1992-01-18','Islam','Wanita','199201181992011001','III/d','S2 Ilmu Kesehatan Anak','Belum Menikah','Dokter Spesialis Anak','PNS',NULL,0,'Jl. Ahmad Yani No. 7, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(6,'2026-05-19 11:09:28','Dr. Hendrik Wambrauw,Sp.THT','Timika','1983-09-30','Kristen','Pria','198309301983091001','IV/d','S2 THT-KL','Menikah','Dokter Spesialis THT','PNS',NULL,4,'Jl. Diponegoro No. 15, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(7,'2026-05-19 11:09:28','Dr. Rina Kusumawati,Sp.M','Semarang','1991-04-12','Islam','Wanita','199104121991041001','III/c','S2 Ilmu Mata','Belum Menikah','Dokter Spesialis Mata','Honorer',NULL,0,'Jl. Gatot Subroto No. 3, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(8,'2026-05-19 11:09:28','Dr. Yusuf Maulana','Jakarta','1987-06-20','Islam','Pria','198706201987061001','III/b','S1 Kedokteran','Menikah','Dokter Umum','PNS',NULL,2,'Jl. Sudirman No. 45, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(9,'2026-05-19 11:09:28','Dr. Angelina Murib','Wamena','1994-12-03','Kristen','Wanita','199412031994121001','III/a','S1 Kedokteran','Belum Menikah','Dokter Umum IGD','Honorer',NULL,0,'Jl. Pahlawan No. 20, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(10,'2026-05-19 11:09:28','Dr. Andi Saputra','Makassar','1986-02-28','Islam','Pria','198602281986021001','III/c','S1 Kedokteran','Menikah','Dokter Umum Puskesmas','CPNS',NULL,1,'Jl. Veteran No. 11, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(11,'2026-05-19 11:09:28','Dr. Christina Yikwa','Nabire','1993-08-14','Kristen','Wanita','199308141993081001','III/b','S1 Kedokteran','Belum Menikah','Dokter Umum Poliklinik','Honorer',NULL,0,'Jl. Kenari No. 6, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(12,'2026-05-19 11:09:28','Ns. I Made Surya Pratama, S.Kep','Denpasar','1995-05-10','Hindu','Pria','199505101995051001','III/a','S1 Keperawatan','Belum Menikah','Perawat Pelaksana IGD','PNS',NULL,0,'Jl. Kamboja No. 9, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(13,'2026-05-19 11:09:28','Ns. Oktavia Rahmawati, S.Kep','Surabaya','1996-09-25','Islam','Wanita','199609251996091001','III/a','S1 Keperawatan','Menikah','Perawat Pelaksana Rawat Inap','Honorer',NULL,1,'Jl. Melati No. 18, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(14,'2026-05-19 11:09:28','Ns. Filemon Tabuni, S.Kep','Jayawijaya','1994-03-07','Kristen','Pria','199403071994031001','II/d','S1 Keperawatan','Belum Menikah','Perawat Pelaksana Rawat Jalan','Honorer',NULL,0,'Jl. Anggrek No. 4, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(15,'2026-05-19 11:09:28','Ns. Putri Handayani, S.Kep','Yogyakarta','1997-01-30','Islam','Wanita','199701301997011001','II/c','S1 Keperawatan','Belum Menikah','Perawat Pelaksana VK','Kontrak',NULL,0,'Jl. Dahlia No. 22, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(16,'2026-05-19 11:09:28','Ns. Markus Wonda, S.Kep','Sentani','1993-11-19','Kristen','Pria','199311191993111001','III/b','S1 Keperawatan','Menikah','Kepala Ruangan Rawat Inap','PNS',NULL,2,'Jl. Mawar No. 30, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(17,'2026-05-19 11:09:28','Bidan Sari Dewi, A.Md.Keb','Palembang','1991-06-08','Islam','Wanita','199106081991061001','III/a','D3 Kebidanan','Menikah','Bidan Pelaksana Poli KIA','PNS',NULL,2,'Jl. Teratai No. 5, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(18,'2026-05-19 11:09:28','Bidan Elisabeth Yikwa, A.Md.Keb','Wamena','1995-10-20','Kristen','Wanita','199510201995101001','II/d','D3 Kebidanan','Belum Menikah','Bidan Pelaksana Persalinan','Honorer',NULL,0,'Jl. Bougenville No. 12, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(19,'2026-05-19 11:09:28','Bidan Ningsih Rahayu, A.Md.Keb','Medan','1990-04-15','Islam','Wanita','199004151990041001','III/b','D3 Kebidanan','Menikah','Koordinator Bidan','PNS',NULL,3,'Jl. Flamboyan No. 8, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(20,'2026-05-19 11:09:28','Apoteker Rina Marlina, S.Farm','Bandung','1989-02-14','Islam','Wanita','198902141989021001','III/c','S1 Farmasi','Menikah','Apoteker Instalasi Farmasi','PNS',NULL,1,'Jl. Kenanga No. 17, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(21,'2026-05-19 11:09:28','TT. Febrian Kogoya, A.Md.Farm','Timika','1996-07-05','Kristen','Pria','199607051996071001','II/c','D3 Farmasi','Belum Menikah','Asisten Apoteker','Honorer',NULL,0,'Jl. Rajawali No. 33, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(22,'2026-05-19 11:09:28','Apoteker Diah Puspita, S.Farm','Solo','1993-12-22','Islam','Wanita','199312221993121001','III/b','S1 Farmasi','Belum Menikah','Kepala Instalasi Farmasi','PNS',NULL,0,'Jl. Nusa Indah No. 10, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(23,'2026-05-19 11:09:28','Analis Medis Jhonson Murib, A.Md.AK','Jayapura','1992-05-18','Kristen','Pria','199205181992051001','III/a','D3 Analis Kesehatan','Menikah','Analis Laboratorium','PNS',NULL,2,'Jl. Percetakan No. 14, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(24,'2026-05-19 11:09:28','Analis Medis Ratna Wulandari, A.Md.AK','Padang','1994-08-09','Islam','Wanita','199408091994081001','II/d','D3 Analis Kesehatan','Belum Menikah','Analis Laboratorium','Honorer',NULL,0,'Jl. Srigunting No. 21, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(25,'2026-05-19 11:09:28','Radiografer Bambang Sutrisno, S.Tr.Rad','Semarang','1990-10-03','Islam','Pria','199010031990101001','III/b','D4 Radiodiagnostik','Menikah','Radiografer Instalasi Radiologi','PNS',NULL,2,'Jl. Garuda No. 16, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(26,'2026-05-19 11:09:28','Radiografer Yuliana Kogoya, S.Tr.Rad','Timika','1997-03-25','Kristen','Wanita','199703251997031001','II/b','D4 Radiodiagnostik','Belum Menikah','Radiografer','Honorer',NULL,0,'Jl. Elang No. 2, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(27,'2026-05-19 11:09:28','Agustina Wambrauw, S.AP','Timika','1988-07-11','Kristen','Wanita','198807111988071001','IV/a','S1 Administrasi Publik','Menikah','Kepala Bagian Umum','PNS',NULL,3,'Jl. Kasuari No. 28, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(28,'2026-05-19 11:09:28','Heri Susanto, S.Kom','Surabaya','1991-12-01','Islam','Pria','199112011991121001','III/c','S1 Sistem Informasi','Menikah','Staf IT & SIMRS','PNS',NULL,1,'Jl. Cendrawasih No. 35, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(29,'2026-05-19 11:09:28','Maria Goretti Kalami, S.E','Nabire','1987-04-27','Katolik','Wanita','198704271987041001','IV/b','S1 Akuntansi','Menikah','Kepala Bagian Keuangan','PNS',NULL,2,'Jl. Kutilang No. 19, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(30,'2026-05-19 11:09:28','Rahmat Hidayat, S.AP','Makassar','1995-01-15','Islam','Pria','199501151995011001','II/d','S1 Administrasi Publik','Belum Menikah','Staf Kepegawaian','CPNS',NULL,0,'Jl. Walet No. 7, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(31,'2026-05-19 11:09:28','Sri Wahyuni, S.Sos','Yogyakarta','1990-09-08','Islam','Wanita','199009081990091001','III/b','S1 Ilmu Komunikasi','Menikah','Staf Humas & Pelayanan','PNS',NULL,1,'Jl. Merpati No. 13, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(32,'2026-05-19 11:09:28','Yohanes Pigai, SE','Wamena','1992-06-19','Kristen','Pria','199206191992061001','III/a','S1 Ekonomi','Menikah','Staf Pengadaan & Logistik','PNS',NULL,2,'Jl. Pipit No. 26, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(33,'2026-05-19 11:09:28','Fisioterapis Dwi Nugroho, S.Ft','Malang','1993-08-03','Islam','Pria','199308031993081001','III/a','S1 Fisioterapi','Belum Menikah','Fisioterapis Instalasi Rehabilitasi','Honorer',NULL,0,'Jl. Kakatua No. 11, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(34,'2026-05-19 11:09:28','Ahli Gizi Nuraini Fitri, S.Gz','Banjarmasin','1994-11-28','Islam','Wanita','199411281994111001','III/a','S1 Gizi','Belum Menikah','Ahli Gizi Instalasi Gizi','Honorer',NULL,0,'Jl. Jalak Harupat No. 4, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28'),
(35,'2026-05-19 11:09:28','Sanitarian Abdul Rahman, A.Md.Kes','Gorontalo','1991-03-16','Islam','Pria','199103161991031001','III/b','D3 Kesehatan Lingkungan','Menikah','Sanitarian Instalasi Sanitasi','PNS',NULL,2,'Jl. Rawa Badung No. 9, Timika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-19 11:09:28','2026-05-19 11:09:28');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `role` enum('admin','operator','viewer') DEFAULT 'operator',
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe','Administrator','admin',NULL,NULL,'2026-05-19 10:17:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-19 21:04:21
