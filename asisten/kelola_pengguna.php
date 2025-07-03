<?php
session_start();
require_once '../config.php';

// Cek login & role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'asisten' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Kelola Akun Pengguna';
require_once 'templates/header.php';

// Ambil data user
$query = "SELECT id, nama, email, role FROM users ORDER BY role, nama";
$result = $conn->query($query);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">👥 Manajemen Akun Pengguna</h2>

    <?php if (isset($_SESSION['status'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            <?= $_SESSION['status']; unset($_SESSION['status']); ?>
        </div>
    <?php endif; ?>

    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Nama</th>
                <th class="p-2 border">Email</th>
                <th class="p-2 border">Role</th>
                <th class="p-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2 border"><?= htmlspecialchars($user['nama']) ?></td>
                    <td class="p-2 border"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="p-2 border"><?= htmlspecialchars($user['role']) ?></td>
                    <td class="p-2 border">
                        <div class="flex space-x-3">
                            <a href="edit_pengguna.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="hapus_pengguna.php?id=<?= $user['id'] ?>"
                                   class="text-red-600 hover:underline"
                                   onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                   Hapus
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>
