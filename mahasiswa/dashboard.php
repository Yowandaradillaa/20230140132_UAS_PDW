<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';

// Ambil daftar praktikum yang diikuti mahasiswa
$stmt = $conn->prepare("
    SELECT mp.id, mp.nama_mata_praktikum 
    FROM praktikum_mahasiswa pm 
    JOIN mata_praktikum mp ON mp.id = pm.mata_praktikum_id 
    WHERE pm.mahasiswa_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$praktikum = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title><?= $pageTitle ?> - SIMPRAK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-blue-600 text-white p-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-xl font-bold">SIMPRAK - Mahasiswa</div>
        <ul class="flex gap-6 text-sm">
            <li><a href="dashboard.php" class="underline">Dashboard</a></li>
            <li><a href="cari_praktikum.php" class="hover:underline">Katalog</a></li>
            <li><a href="../logout.php" class="hover:underline">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Konten -->
<div class="max-w-6xl mx-auto px-6 py-10">

    <!-- Salam -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h2>
        <p class="text-gray-600 mt-1">
            Berikut adalah daftar mata praktikum yang kamu ikuti. Kamu bisa mengumpulkan laporan, melihat nilai, dan mengunduh materi di sana.
        </p>
    </div>

    <!-- Status sukses daftar -->
    <?php if (isset($_SESSION['status'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['status']) ?>
        </div>
        <?php unset($_SESSION['status']); ?>
    <?php endif; ?>

    <!-- Praktikum Diikuti -->
    <?php if ($praktikum->num_rows === 0): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg shadow mb-6">
            <p>Kamu belum mendaftar praktikum.</p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($row['nama_mata_praktikum']) ?></h3>
                    <p class="text-gray-500 text-sm mt-1 mb-3">Praktikum aktif</p>
                    <a href="praktikum_detail.php?id=<?= $row['id'] ?>" class="text-blue-600 text-sm font-medium hover:underline">
                        ➜ Lihat Detail
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Tombol Cari Praktikum -->
    <div class="text-center mt-8">
        <a href="cari_praktikum.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
            🔍 Cari Praktikum Lainnya
        </a>
    </div>

</div>

<!-- Footer -->
<footer class="text-center text-sm text-gray-500 p-4 mt-12">
    &copy; 2025 Sistem Informasi Manajemen Praktikum (SIMPRAK)
</footer>

</body>
</html>
