<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Cari Praktikum';
require_once 'templates/header_mahasiswa.php';

$cari = $_GET['cari'] ?? '';
$praktikum = [];

if (!empty($cari)) {
    $sql = "SELECT DISTINCT id, nama_mata_praktikum FROM mata_praktikum WHERE nama_mata_praktikum LIKE ?";
    $stmt = $conn->prepare($sql);
    $param = "%$cari%";
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $praktikum = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded-lg my-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">🔍 Cari Mata Praktikum</h2>

    <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
        <input type="text" name="cari" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-blue-500"
               placeholder="Contoh: Jaringan Komputer" value="<?= htmlspecialchars($cari) ?>">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Cari
        </button>
    </form>

    <?php if (!empty($cari)): ?>
        <?php if (count($praktikum) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($praktikum as $p): ?>
                    <div class="border p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($p['nama_mata_praktikum']) ?></h3>
                        <form action="daftar_praktikum.php" method="post" class="mt-2">
                            <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                                Daftar
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center">Tidak ditemukan mata praktikum dengan kata kunci tersebut.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
