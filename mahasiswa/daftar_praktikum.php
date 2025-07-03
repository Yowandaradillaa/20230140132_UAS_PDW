<?php
session_start();
require_once '../config.php';

// Cek apakah user login dan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

// Jika form daftar dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $praktikum_id = intval($_POST['praktikum_id']);
    $mahasiswa_id = $_SESSION['user_id'];

    $cek = $conn->prepare("SELECT * FROM praktikum_mahasiswa WHERE mahasiswa_id = ? AND mata_praktikum_id = ?");
    $cek->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['flash_message'] = "❗ Kamu sudah terdaftar di praktikum ini.";
    } else {
        $insert = $conn->prepare("INSERT INTO praktikum_mahasiswa (mahasiswa_id, mata_praktikum_id) VALUES (?, ?)");
        $insert->bind_param("ii", $mahasiswa_id, $praktikum_id);
        if ($insert->execute()) {
            $_SESSION['flash_message'] = "✅ Berhasil mendaftar praktikum!";
        } else {
            $_SESSION['flash_message'] = "❌ Gagal mendaftar praktikum.";
        }
    }

    header("Location: dashboard.php");
    exit();
}

// Jika buka halaman GET untuk menampilkan form pencarian
$pageTitle = 'Cari Praktikum';
require_once 'templates/header_mahasiswa.php';

$cari = $_GET['q'] ?? '';
$sql = "SELECT * FROM mata_praktikum WHERE nama_mata_praktikum LIKE ?";
$stmt = $conn->prepare($sql);
$param = "%$cari%";
$stmt->bind_param("s", $param);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded">
    <h2 class="text-2xl font-bold mb-4">Daftar Mata Praktikum</h2>

    <!-- Form pencarian -->
    <form method="get" class="mb-6 flex gap-4">
        <input type="text" name="q" class="border px-3 py-2 rounded w-full" placeholder="Masukkan nama mata praktikum" value="<?= htmlspecialchars($cari); ?>">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Cari</button>
    </form>

    <?php if (count($praktikum) === 0): ?>
        <p class="text-gray-500">Tidak ada praktikum yang ditemukan.</p>
    <?php endif; ?>

    <?php foreach ($praktikum as $p): ?>
        <div class="border-b py-3">
            <div class="font-semibold text-lg"><?= htmlspecialchars($p['nama_mata_praktikum']); ?></div>
            <form action="daftar_praktikum.php" method="post" class="mt-2">
                <input type="hidden" name="praktikum_id" value="<?= $p['id']; ?>">
                <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded">Daftar</button>
            </form>
        </div>
    <?php endforeach; ?>

    <!-- Tombol kembali -->
    <div class="mt-6">
        <a href="dashboard.php" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
