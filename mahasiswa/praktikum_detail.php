<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$praktikum_id = $_GET['id'] ?? null;
if (!$praktikum_id) {
    header("Location: dashboard.php");
    exit();
}

// Ambil info praktikum
$stmt = $conn->prepare("SELECT nama_mata_praktikum FROM mata_praktikum WHERE id = ?");
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

if (!$praktikum) {
    echo "Praktikum tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">

<div class="max-w-5xl mx-auto bg-white shadow p-6 rounded-lg">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">📘 Praktikum: <?= htmlspecialchars($praktikum['nama_mata_praktikum']) ?></h2>
        <a href="dashboard.php" class="text-sm text-blue-600 hover:underline">← Kembali ke Dashboard</a>
    </div>

    <!-- 📥 Materi Modul -->
    <div class="mb-8">
        <h3 class="text-lg font-bold mb-2 text-blue-600">📥 Materi Modul</h3>
        <?php
        $modulQuery = $conn->prepare("SELECT * FROM modul WHERE mata_praktikum_id = ?");
        $modulQuery->bind_param("i", $praktikum_id);
        $modulQuery->execute();
        $modulResult = $modulQuery->get_result();
        if ($modulResult->num_rows === 0): ?>
            <p class="text-gray-500">Belum ada materi yang tersedia.</p>
        <?php else: ?>
            <ul class="list-disc list-inside">
                <?php while ($modul = $modulResult->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($modul['judul']) ?> –
                        <a href="../uploads/<?= $modul['file'] ?>" class="text-blue-600 hover:underline" download>Unduh</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- 📄 Daftar Tugas & Upload -->
    <div>
        <h3 class="text-lg font-bold mb-2 text-blue-600">📄 Tugas & Laporan</h3>
        <?php
        $tugasQuery = $conn->prepare("
            SELECT t.id, t.judul, t.deskripsi, t.tenggat, m.judul AS nama_modul,
                   l.file AS file_laporan, l.nilai, l.komentar
            FROM tugas t
            JOIN modul m ON t.modul_id = m.id
            LEFT JOIN laporan l ON t.id = l.tugas_id AND l.mahasiswa_id = ?
            WHERE m.mata_praktikum_id = ?
            ORDER BY t.tenggat ASC
        ");
        $tugasQuery->bind_param("ii", $_SESSION['user_id'], $praktikum_id);
        $tugasQuery->execute();
        $tugasResult = $tugasQuery->get_result();

        if ($tugasResult->num_rows === 0): ?>
            <p class="text-gray-500">Belum ada tugas untuk praktikum ini.</p>
        <?php else: ?>
            <?php while ($tugas = $tugasResult->fetch_assoc()): ?>
                <div class="border-t pt-4 mt-6">
                    <h4 class="text-lg font-semibold text-gray-800">
                        <?= htmlspecialchars($tugas['judul']) ?>
                        <span class="text-sm text-gray-500">[<?= htmlspecialchars($tugas['nama_modul']) ?>]</span>
                    </h4>
                    <p class="text-gray-600 text-sm mb-1">🗓 Tenggat: <?= date('d M Y H:i', strtotime($tugas['tenggat'])) ?></p>
                    <p class="text-gray-700 mb-2"><?= nl2br(htmlspecialchars($tugas['deskripsi'])) ?></p>

                    <?php if ($tugas['file_laporan']): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-2">
                            <p class="text-green-700">✔ Laporan telah dikumpulkan</p>
                            <a href="../uploads/<?= $tugas['file_laporan'] ?>" class="text-blue-600 underline" download>Lihat Laporan</a>
                        </div>
                        <p>🧾 Nilai: <strong><?= $tugas['nilai'] ?? 'Belum dinilai' ?></strong></p>
                        <?php if (!empty($tugas['komentar'])): ?>
                            <p>💬 Feedback: <em><?= htmlspecialchars($tugas['komentar']) ?></em></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <form action="upload_laporan.php" method="post" enctype="multipart/form-data" class="mt-3">
                            <input type="hidden" name="tugas_id" value="<?= $tugas['id'] ?>">
                            <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">


                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Kumpulkan Laporan
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
