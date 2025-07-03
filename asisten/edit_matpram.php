<?php
require_once '../config.php';
session_start();

// Cek akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();
$stmt->close();

if (!$tugas) {
    die("Tugas tidak ditemukan.");
}

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $modul_id = intval($_POST['modul_id']);
    $tenggat = $_POST['tenggat'];

    if ($judul === '' || empty($tenggat)) {
        $errors[] = "Judul dan tenggat wajib diisi.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE tugas SET judul = ?, modul_id = ?, tenggat = ? WHERE id = ?");
        $stmt->bind_param("sisi", $judul, $modul_id, $tenggat, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: matapraktikum.php?status=edit");
        exit();
    }
}

// Ambil daftar modul
$modulResult = $conn->query("SELECT id, judul FROM modul ORDER BY judul ASC");
$modulList = $modulResult ? $modulResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mata Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4 text-gray-800">✏️ Edit Mata Praktikum</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded border border-red-400">
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Judul Tugas:</label>
                <input type="text" name="judul" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($tugas['judul']) ?>" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Modul:</label>
                <select name="modul_id" class="w-full border px-3 py-2 rounded" required>
                    <?php foreach ($modulList as $modul): ?>
                        <option value="<?= $modul['id'] ?>" <?= $modul['id'] == $tugas['modul_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($modul['judul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Tenggat:</label>
                <input type="datetime-local" name="tenggat" class="w-full border px-3 py-2 rounded" value="<?= date('Y-m-d\TH:i', strtotime($tugas['tenggat'])) ?>" required>
            </div>

            <div class="flex justify-between items-center">
                <a href="daftar_matpram.php" class="text-gray-600 hover:underline text-sm">← Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>
