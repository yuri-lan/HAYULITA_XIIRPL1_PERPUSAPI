<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=data_buku.xls");

echo "No\tKode\tJudul\tPenulis\tKategori\tTahun\tPenerbit\n";
$q = mysqli_query($conn, "SELECT * FROM books");
$no = 1;
while ($r = mysqli_fetch_assoc($q)) {
    echo "$no\t{$r['kode_buku']}\t{$r['judul']}\t{$r['penulis']}\t{$r['kategori']}\t{$r['tahun_terbit']}\t{$r['penerbit']}\n";
    $no++;
}
?>