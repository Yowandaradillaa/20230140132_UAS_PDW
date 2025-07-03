<?php
session_start();
require_once '../config.php';

// Cek login & role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'asisten' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

// Ambil ID user dari URL
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: kelola_pengguna.php");
    exit();
}

// Ambil data user yang akan diedit
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['status'] = "Pengguna tidak ditemukan.";
    header("Location: kelola_pengguna.php");
    exit();
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $update = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
    $update->bind_param("sssi", $nama, $email, $role, $id);

    if ($update->execute()) {
        $_SESSION['status'] = "Pengguna berhasil diperbarui.";
        header("Location: kelola_pengguna.php");
        exit();
    } else {
        $error = "Gagal memperbarui data.";
    }
}

$pageTitle = 'Edit Pengguna';
require_once 'templates/header.php';
?>

<div class="max-w-xl mx-auto bg-white shadow p-6 rounded-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">✏️ Edit Pengguna</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nama</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full border px-4 py-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full border px-4 py-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Role</label>
            <select name="role" class="w-full border px-4 py-2 rounded" required>
                <option value="mahasiswa" <?= $user['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                <option value="asisten" <?= $user['role'] === 'asisten' ? 'selected' : '' ?>>Asisten</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="flex justify-between items-center mt-4">
            <a href="kelola_pengguna.php" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
