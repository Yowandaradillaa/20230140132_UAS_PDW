<?php
session_start();
require_once '../config.php';

// Cek role asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <!-- Tombol Navigasi -->
    <div class="flex justify-between items-center mb-6">
        <a href="dashboard.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
            ← Dashboard
        </a>
        <a href="tambah_modul.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Tambah Modul
        </a>
    </div>

    <!-- Tabel Modul -->
    <?php
    // Ambil semua modul beserta info nama mata praktikum
    $sql = "
        SELECT m.id, m.judul, m.deskripsi, m.tanggal_upload, p.nama_mata_praktikum 
        FROM modul m
        JOIN mata_praktikum p ON m.mata_praktikum_id = p.id
        ORDER BY m.tanggal_upload DESC
    ";
    $result = $conn->query($sql);

    if ($result->num_rows === 0): ?>
        <p class="text-gray-500 text-center">Belum ada modul.</p>
    <?php else: ?>
        <table class="w-full table-auto border border-gray-300">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 border">Judul</th>
                    <th class="px-4 py-2 border">Deskripsi</th>
                    <th class="px-4 py-2 border">Mata Praktikum</th>
                    <th class="px-4 py-2 border">Tanggal Upload</th>
                    <th class="px-4 py-2 border text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['nama_mata_praktikum']) ?></td>
                        <td class="px-4 py-2"><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></td>
                        <td class="px-4 py-2 text-center space-x-3">
                            <a href="edit_modul.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                            <a href="hapus_modul.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus modul ini?')" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
