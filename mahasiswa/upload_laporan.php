<?php
session_start();
require_once '../config.php';

// Cek login dan role mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$id_mahasiswa = $_SESSION['user_id'];
$message = '';
$success = false;

// Ambil daftar modul
$modulList = [];
$modulRes = $conn->query("SELECT id, judul FROM modul ORDER BY judul");
if ($modulRes && $modulRes->num_rows > 0) {
    $modulList = $modulRes->fetch_all(MYSQLI_ASSOC);
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tugas_id = $_POST['tugas_id'] ?? null;
    $id_modul = $_POST['modul'] ?? '';
    $file = $_FILES['laporan'];

    if (empty($id_modul) || empty($tugas_id) || $file['error'] != 0) {
    } else {
        $namaFile = basename($file['name']);
        $targetDir = "../uploads/";
        $targetPath = $targetDir . time() . "_" . $namaFile;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $fileNameDB = basename($targetPath);

            $stmt = $conn->prepare("INSERT INTO laporan (mahasiswa_id, modul_id, tugas_id, file) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $id_mahasiswa, $id_modul, $tugas_id, $fileNameDB);

            if ($stmt->execute()) {
                $message = "✅ Tugas berhasil diunggah.";
                $success = true;
            } else {
                $message = "❌ Gagal menyimpan laporan ke database: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "❌ Gagal mengupload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Upload Laporan Praktikum</h2>
        <a href="dashboard.php" class="text-sm text-gray-600 hover:underline">← Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-4 text-center text-sm font-medium px-4 py-2 rounded 
            <?php echo $success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form action="upload_laporan.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="modul" class="block text-sm font-medium text-gray-700">Pilih Modul</label>
                <select id="modul" name="modul" class="w-full border border-gray-300 rounded px-4 py-2" required>
                    <option value="">-- Pilih Modul --</option>
                    <?php foreach ($modulList as $mod): ?>
                        <option value="<?php echo $mod['id']; ?>"><?php echo htmlspecialchars($mod['judul']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="tugas_id" class="block text-sm font-medium text-gray-700">Pilih Tugas</label>
                <input type="number" name="tugas_id" id="tugas_id" class="w-full border border-gray-300 rounded px-4 py-2" required>
                <!-- Ganti input ini dengan select jika kamu punya daftar tugas dari database -->
            </div>

            <div>
                <label for="laporan" class="block text-sm font-medium text-gray-700">File Laporan (.pdf/.docx)</label>
                <input type="file" name="laporan" id="laporan" accept=".pdf,.doc,.docx" class="w-full border border-gray-300 rounded px-4 py-2" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Upload</button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
