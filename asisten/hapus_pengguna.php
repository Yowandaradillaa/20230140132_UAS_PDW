<?php
session_start();
require_once '../config.php';

// Pastikan hanya admin/asisten yang bisa mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'asisten')) {
    header("Location: ../login.php");
    exit();
}

// Pastikan ada ID yang dikirim
if (!isset($_GET['id'])) {
    header("Location: kelola_pengguna.php");
    exit();
}

$id = intval($_GET['id']);

// Cegah agar user tidak bisa menghapus dirinya sendiri
if ($id == $_SESSION['user_id']) {
    $_SESSION['status'] = "Kamu tidak bisa menghapus akunmu sendiri.";
    header("Location: kelola_pengguna.php");
    exit();
}

// Hapus pengguna
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $_SESSION['status'] = "Pengguna berhasil dihapus.";
} else {
    $_SESSION['status'] = "Gagal menghapus pengguna.";
}

header("Location: kelola_pengguna.php");
exit();
?>
