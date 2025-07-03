<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}
$pageTitle = 'Materi';
require_once 'templates/header_mahasiswa.php';

$sql = "SELECT * FROM materi ORDER BY tanggal_upload DESC";
$result = $conn->query($sql);
?>

<div class="max-w-3xl mx-auto bg-white p-6 shadow rounded">
    <h2 class="text-2xl font-bold mb-4">Daftar Materi</h2>
    <?php while ($m = $result->fetch_assoc()): ?>
        <div class="border-b py-3">
            <div class="font-semibold"><?php echo htmlspecialchars($m['judul']); ?></div>
            <a href="../uploads/materi/<?php echo $m['file']; ?>" class="text-blue-600 hover:underline" download>Unduh Materi</a>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
