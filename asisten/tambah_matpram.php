<?php
session_start();
require_once '../config.php';

// Cek role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// Ambil semua modul untuk dropdown
$modul = [];
$query = $conn->query("SELECT id, judul FROM modul ORDER BY tanggal_upload DESC");
if ($query && $query->num_rows > 0) {
    $modul = $query->fetch_all(MYSQLI_ASSOC);
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tenggat = $_POST['tenggat'];
    $modul_id = intval($_POST['modul_id']);

    if (empty($judul) || empty($tenggat) || $modul_id === 0) {
        $message = "Harap lengkapi semua field yang wajib diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tugas (judul, deskripsi, tenggat, modul_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $judul, $deskripsi, $tenggat, $modul_id);
        if ($stmt->execute()) {
            header("Location: matapraktikum.php?status=berhasil");
            exit();
        } else {
            $message = "Gagal menambahkan tugas: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mata Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">Tambah Mata Praktikum ke Modul</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-red-100 text-red-700 border border-red-400 p-3 rounded mb-4">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="tambah_matpram.php" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">JuduL *</label>
            <input type="text" name="judul" class="w-full px-4 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="w-full px-4 py-2 border rounded"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tenggat Waktu *</label>
            <input type="datetime-local" name="tenggat" class="w-full px-4 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Pilih Modul *</label>
            <select name="modul_id" class="w-full px-4 py-2 border rounded" required>
                <option value="">-- Pilih Modul --</option>
                <?php foreach ($modul as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['judul']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-between items-center">
            <a href="tugas.php" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 text-gray-800">← Kembali</a>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">Simpan Tugas</button>
        </div>
    </form>
</div>

</body>
</html>