<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Ambil semua tugas
$sql = "SELECT t.*, m.judul AS modul FROM tugas t JOIN modul m ON t.modul_id = m.id ORDER BY t.tenggat ASC";
$result = $conn->query($sql);
$tugasList = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Mata Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(judul, url) {
            if (confirm('Yakin ingin menghapus tugas "' + judul + '"?')) {
                window.location.href = url;
            }
        }
    </script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">📌 Daftar Mata Praktikum</h2>
        <a href="tambah_matpram.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah Mata Praktikum</a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'berhasil'): ?>
        <div class="bg-green-100 text-green-700 p-3 border border-green-400 rounded mb-4">
            Mata Praktikum berhasil ditambahkan!
        </div>
    <?php endif; ?>

    <?php if (count($tugasList) === 0): ?>
        <p class="text-gray-500">Belum ada matprak yang ditambahkan.</p>
    <?php else: ?>
        <table class="w-full table-auto border border-gray-300 text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 border">Judul</th>
                    <th class="px-4 py-2 border">Modul</th>
                    <th class="px-4 py-2 border">Tenggat</th>
                    <th class="px-4 py-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tugasList as $tugas): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2"><?= htmlspecialchars($tugas['judul']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($tugas['modul']) ?></td>
                        <td class="px-4 py-2"><?= date('d M Y H:i', strtotime($tugas['tenggat'])) ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="edit_matpram.php?id=<?= $tugas['id'] ?>" class="text-yellow-600 hover:underline">Edit</a>
                            <a href="javascript:void(0)" onclick="confirmDelete('<?= htmlspecialchars($tugas['judul']) ?>', 'hapus_matpram.php?id=<?= $tugas['id'] ?>')" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Tombol Kembali ke Dashboard -->
<div class="mt-6 text-center">
    <a href="dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
        ← Kembali ke Dashboard
    </a>
</div>
</body>
</html>
