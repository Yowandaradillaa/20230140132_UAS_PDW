<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$message = '';

if (!$id) {
    header("Location: modul.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);

    if ($judul) {
        $stmt = $conn->prepare("UPDATE modul SET judul = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssi", $judul, $deskripsi, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: modul.php?status=edited");
        exit();
    } else {
        $message = "Judul tidak boleh kosong.";
    }
}

// Ambil data modul
$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Edit Modul</h2>

        <?php if (!empty($message)): ?>
            <p class="text-red-600 mb-4"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="" method="post" class="space-y-4">
            <div>
                <label for="judul" class="block text-gray-700 font-medium">Judul</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($data['judul']); ?>" class="w-full border px-4 py-2 rounded" required>
            </div>
            <div>
                <label for="deskripsi" class="block text-gray-700 font-medium">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full border px-4 py-2 rounded"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
            </div>
            <div class="flex justify-between">
                <a href="modul.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">← Kembali</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>
