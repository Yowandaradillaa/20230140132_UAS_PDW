<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$message = '';

// Ambil daftar mata praktikum
$praktikum = [];
$praktikumQuery = $conn->query("SELECT id, nama_mata_praktikum FROM mata_praktikum");
if ($praktikumQuery && $praktikumQuery->num_rows > 0) {
    $praktikum = $praktikumQuery->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $mata_praktikum_id = isset($_POST['mata_praktikum_id']) ? intval($_POST['mata_praktikum_id']) : 0;
    $file_name = '';

    // Validasi dasar
    if (empty($judul) || $mata_praktikum_id === 0) {
        $message = "Judul dan mata praktikum harus diisi.";
    } else {
        // Handle upload file
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
            $fileType = mime_content_type($_FILES['file']['tmp_name']);
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileType, $allowedTypes)) {
                $message = "File harus berupa PDF atau DOCX.";
            } elseif ($_FILES['file']['size'] > $maxSize) {
                $message = "Ukuran file maksimal 2MB.";
            } else {
                $file_name = time() . '_' . basename($_FILES['file']['name']);
                $destination = '../uploads/' . $file_name;
                move_uploaded_file($_FILES['file']['tmp_name'], $destination);
            }
        }

        if (empty($message)) {
            $sql = "INSERT INTO modul (judul, deskripsi, mata_praktikum_id, file) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $judul, $deskripsi, $mata_praktikum_id, $file_name);
            if ($stmt->execute()) {
                header("Location: modul.php?status=success");
                exit();
            } else {
                $message = "Gagal menambahkan modul. " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-2xl bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-3xl font-bold text-blue-600 mb-6 text-center">Tambah Modul Baru</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="tambah_modul.php" method="post" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label for="judul" class="block text-gray-700 font-semibold mb-1">Judul Modul <span class="text-red-500">*</span></label>
            <input type="text" id="judul" name="judul" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
        </div>

        <div>
            <label for="deskripsi" class="block text-gray-700 font-semibold mb-1">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="4"></textarea>
        </div>

        <div>
            <label for="mata_praktikum_id" class="block text-gray-700 font-semibold mb-1">Mata Praktikum <span class="text-red-500">*</span></label>
            <select id="mata_praktikum_id" name="mata_praktikum_id" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                <option value="">-- Pilih Mata Praktikum --</option>
                <?php foreach ($praktikum as $row): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_mata_praktikum']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="file" class="block text-gray-700 font-semibold mb-1">Upload File Materi (PDF/DOCX)</label>
            <input type="file" id="file" name="file" accept=".pdf,.docx,.doc" class="block w-full border border-gray-300 rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB.</p>
        </div>

        <div class="flex justify-between items-center pt-4">
            <a href="modul.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">← Kembali</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition">Simpan</button>
        </div>
    </form>
</div>

</body>
</html>
