CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. Tabel mata_praktikum
CREATE TABLE mata_praktikum (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_mata_praktikum VARCHAR(100) NOT NULL,
    asisten_id INT(11)
);

-- 2. Tabel modul
CREATE TABLE modul (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tanggal_upload TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    file VARCHAR(255),
    mata_praktikum_id INT(11),
    FOREIGN KEY (mata_praktikum_id) REFERENCES mata_praktikum(id) ON DELETE CASCADE
);

-- 3. Tabel tugas
CREATE TABLE tugas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    modul_id INT(11) NOT NULL,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tenggat DATETIME,
    tanggal_upload TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modul_id) REFERENCES modul(id) ON DELETE CASCADE
);

-- 4. Tabel praktikum_mahasiswa
CREATE TABLE praktikum_mahasiswa (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT(11) NOT NULL,
    mata_praktikum_id INT(11),
    FOREIGN KEY (mata_praktikum_id) REFERENCES mata_praktikum(id) ON DELETE CASCADE
);

-- 5. Tabel laporan
CREATE TABLE laporan (
    modul_id INT(11) NOT NULL,
    mahasiswa_id INT(11) NOT NULL,
    praktikum_mahasiswa_id INT(11),
    tugas_id INT(11) NOT NULL,
    judul VARCHAR(100) NOT NULL,
    file VARCHAR(255),
    nilai VARCHAR(10),
    komentar TEXT,
    tanggal_upload TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (modul_id, mahasiswa_id, tugas_id),
    FOREIGN KEY (modul_id) REFERENCES modul(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (praktikum_mahasiswa_id) REFERENCES praktikum_mahasiswa(id) ON DELETE SET NULL,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE
);
