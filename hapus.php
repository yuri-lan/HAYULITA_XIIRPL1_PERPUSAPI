<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: buku.php"); exit; }

$cek = mysqli_query($conn, "SELECT id, cover FROM books WHERE id = $id");
if (mysqli_num_rows($cek) == 0) { header("Location: buku.php?status=notfound"); exit; }
$row = mysqli_fetch_assoc($cek);

if ($row['cover'] && file_exists(__DIR__ . '/uploads/' . $row['cover'])) {
    unlink(__DIR__ . '/uploads/' . $row['cover']);
}

$stmt = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: buku.php?status=hapus");
} else {
    header("Location: buku.php?status=gagal");
}
exit;
?>