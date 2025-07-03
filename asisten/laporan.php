<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../config.php';

// Cek role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Simpan nilai jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tugas_id'], $_POST['mahasiswa_id'])) {
    $tugas_id = $_POST['tugas_id'];
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $nilai = $_POST['nilai'] ?? '';
    $komentar = $_POST['komentar'] ?? '';

    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, komentar = ? WHERE tugas_id = ? AND mahasiswa_id = ?");
    $stmt->bind_param("ssii", $nilai, $komentar, $tugas_id, $mahasiswa_id);
    $stmt->execute();
    $stmt->close();
}

// Ambil filter dari GET
$filter_modul = $_GET['modul_id'] ?? '';
$filter_mahasiswa = $_GET['mahasiswa_id'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Query filter dinamis
$conditions = [];
if ($filter_modul !== '') {
    $conditions[] = "l.modul_id = " . intval($filter_modul);
}
if ($filter_mahasiswa !== '') {
    $conditions[] = "l.mahasiswa_id = " . intval($filter_mahasiswa);
}
if ($filter_status === 'sudah') {
    $conditions[] = "l.nilai IS NOT NULL AND l.nilai <> ''";
} elseif ($filter_status === 'belum') {
    $conditions[] = "(l.nilai IS NULL OR l.nilai = '')";
}

$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Ambil data laporan
$laporanList = [];
$sql = "
    SELECT 
        l.modul_id,
        l.mahasiswa_id,
        l.tugas_id,
        l.judul,
        l.file,
        l.nilai,
        l.komentar,
        l.tanggal_upload,
        u.nama AS nama_mahasiswa,
        mo.judul AS nama_modul,
        t.judul AS nama_tugas
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul mo ON l.modul_id = mo.id
    JOIN tugas t ON l.tugas_id = t.id
    $whereClause
    ORDER BY l.tanggal_upload DESC
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $laporanList = $result->fetch_all(MYSQLI_ASSOC);
}

// Ambil semua modul & mahasiswa untuk opsi filter
$modulList = $conn->query("SELECT id, judul FROM modul")->fetch_all(MYSQLI_ASSOC);
$mahasiswaList = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa'")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Laporan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Daftar Laporan Mahasiswa</h1>
        <a href="dashboard.php" class="text-blue-600 hover:underline text-sm">← Kembali ke Dashboard</a>
    </div>

    <!-- Filter -->
    <form method="get" class="mb-4 flex flex-wrap gap-4">
        <select name="modul_id" class="border rounded px-2 py-1 text-sm">
            <option value="">Filter Modul</option>
            <?php foreach ($modulList as $modul): ?>
                <option value="<?= $modul['id'] ?>" <?= ($filter_modul == $modul['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($modul['judul']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="mahasiswa_id" class="border rounded px-2 py-1 text-sm">
            <option value="">Filter Mahasiswa</option>
            <?php foreach ($mahasiswaList as $mhs): ?>
                <option value="<?= $mhs['id'] ?>" <?= ($filter_mahasiswa == $mhs['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mhs['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="border rounded px-2 py-1 text-sm">
            <option value="">Status Nilai</option>
            <option value="sudah" <?= ($filter_status == 'sudah') ? 'selected' : '' ?>>Sudah Dinilai</option>
            <option value="belum" <?= ($filter_status == 'belum') ? 'selected' : '' ?>>Belum Dinilai</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
            Terapkan Filter
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2 border">No</th>
                    <th class="p-2 border">Mahasiswa</th>
                    <th class="p-2 border">Modul</th>
                    <th class="p-2 border">Tugas</th>
                    <th class="p-2 border">Judul Laporan</th>
                    <th class="p-2 border">File</th>
                    <th class="p-2 border">Tanggal Upload</th>
                    <th class="p-2 border">Nilai</th>
                    <th class="p-2 border">Komentar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporanList)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">Data tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporanList as $index => $lap): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-2 border text-center"><?= $index + 1 ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($lap['nama_mahasiswa']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($lap['nama_modul']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($lap['nama_tugas']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($lap['judul']) ?></td>
                            <td class="p-2 border text-center">
                                <?php if (!empty($lap['file'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($lap['file']) ?>" class="text-blue-600 underline" download>Download</a>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Tidak ada file</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-2 border"><?= htmlspecialchars($lap['tanggal_upload']) ?></td>
                            <td class="p-2 border">
                                <form method="post" class="flex flex-col space-y-1">
                                    <input type="hidden" name="tugas_id" value="<?= $lap['tugas_id'] ?>">
                                    <input type="hidden" name="mahasiswa_id" value="<?= $lap['mahasiswa_id'] ?>">
                                    <input type="text" name="nilai" value="<?= htmlspecialchars($lap['nilai'] ?? '') ?>" class="border rounded px-2 py-1 text-sm" placeholder="Nilai">
                            </td>
                            <td class="p-2 border">
                                    <input type="text" name="komentar" value="<?= htmlspecialchars($lap['komentar'] ?? '') ?>" class="border rounded px-2 py-1 text-sm" placeholder="Komentar">
                                    <button type="submit" class="mt-1 text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
