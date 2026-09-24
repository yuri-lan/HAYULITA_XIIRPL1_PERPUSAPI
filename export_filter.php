<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$data = json_decode($_POST['data'] ?? '[]', true);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hasil_filter_buku.xls");

echo "No\tKode\tJudul\tPenulis\tKategori\tTahun\tPenerbit\n";

$no = 1;
foreach ($data as $b) {
    echo "$no\t{$b['kode_buku']}\t{$b['judul']}\t{$b['penulis']}\t{$b['kategori']}\t{$b['tahun_terbit']}\t{$b['penerbit']}\n";
    $no++;
}
?>