<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
require('fpdf/fpdf.php');
include 'koneksi.php';

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false); // kita atur page break manual
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN DATA BUKU PERPUSTAKAAN', 0, 1, 'C');
$pdf->Ln(3);

// ================= KONFIGURASI KOLOM =================
$w = [
    'no'       => 10,
    'kode'     => 20,
    'judul'    => 55,
    'penulis'  => 33,
    'kategori' => 27,
    'tahun'    => 15,
    'penerbit' => 30,
];
$lineH = 6;
$marginKiri = 10;
$marginAtasAwal = $pdf->GetY();

// ================= FUNGSI BANTU =================

// Pecah teks jadi array baris sesuai lebar kolom
function pecahTeks($pdf, $teks, $lebar) {
    $kata = explode(' ', $teks);
    $baris = [];
    $current = '';
    foreach ($kata as $k) {
        $coba = $current === '' ? $k : $current . ' ' . $k;
        if ($pdf->GetStringWidth($coba) <= $lebar - 3) {
            $current = $coba;
        } else {
            if ($current !== '') $baris[] = $current;
            $current = $k;
        }
    }
    if ($current !== '') $baris[] = $current;
    if (empty($baris)) $baris[] = '';
    return $baris;
}

// Gambar header tabel
function gambarHeader($pdf, $w, $lineH, $marginKiri) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(143, 169, 152);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY($marginKiri, $pdf->GetY());

    $pdf->Cell($w['no'],       $lineH * 1.5, 'No',       1, 0, 'C', true);
    $pdf->Cell($w['kode'],     $lineH * 1.5, 'Kode',     1, 0, 'C', true);
    $pdf->Cell($w['judul'],    $lineH * 1.5, 'Judul',    1, 0, 'C', true);
    $pdf->Cell($w['penulis'],  $lineH * 1.5, 'Penulis',  1, 0, 'C', true);
    $pdf->Cell($w['kategori'], $lineH * 1.5, 'Kategori', 1, 0, 'C', true);
    $pdf->Cell($w['tahun'],    $lineH * 1.5, 'Tahun',    1, 0, 'C', true);
    $pdf->Cell($w['penerbit'], $lineH * 1.5, 'Penerbit', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
}

// Cetak satu baris data (semua kolom tinggi sama)
function cetakBaris($pdf, $w, $lineH, $marginKiri, $row, $no, $zebra) {
    // 1. Pecah teks kolom yang bisa panjang
    $judul    = pecahTeks($pdf, $row['judul'],    $w['judul']);
    $penulis  = pecahTeks($pdf, $row['penulis'],  $w['penulis']);
    $kategori = pecahTeks($pdf, $row['kategori'], $w['kategori']);
    $penerbit = pecahTeks($pdf, $row['penerbit'], $w['penerbit']);

    // 2. Hitung tinggi baris = max baris x lineH
    $maxBaris = max(
        count($judul),
        count($penulis),
        count($kategori),
        count($penerbit),
        1
    );
    $rowH = $maxBaris * $lineH;

    // 3. Cek page break
    if ($pdf->GetY() + $rowH > 285) {
        $pdf->AddPage();
        gambarHeader($pdf, $w, $lineH, $marginKiri);
    }

    $y0 = $pdf->GetY();

    // 4. Set warna zebra
    if ($zebra) $pdf->SetFillColor(240, 245, 240);
    else        $pdf->SetFillColor(255, 255, 255);

    // 5. Gambar border + isi semua kolom pakai Cell (Y tidak bergeser karena ln=0)
    // Kolom No
    $pdf->SetXY($marginKiri, $y0);
    $pdf->Cell($w['no'], $rowH, $no, 1, 0, 'C', true);

    // Kolom Kode
    $pdf->SetXY($marginKiri + $w['no'], $y0);
    $pdf->Cell($w['kode'], $rowH, $row['kode_buku'], 1, 0, 'C', true);

    // Kolom Judul
    $pdf->SetXY($marginKiri + $w['no'] + $w['kode'], $y0);
    $pdf->Cell($w['judul'], $rowH, '', 1, 0, 'L', true); // border + fill
    $pdf->SetXY($marginKiri + $w['no'] + $w['kode'] + 1.5, $y0 + ($rowH - count($judul) * $lineH) / 2);
    foreach ($judul as $i => $line) {
        $pdf->SetXY($marginKiri + $w['no'] + $w['kode'] + 1.5, $y0 + ($rowH - count($judul) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['judul'] - 3, $lineH, $line, 0, 0, 'L');
    }

    // Kolom Penulis
    $xPenulis = $marginKiri + $w['no'] + $w['kode'] + $w['judul'];
    $pdf->SetXY($xPenulis, $y0);
    $pdf->Cell($w['penulis'], $rowH, '', 1, 0, 'L', true);
    foreach ($penulis as $i => $line) {
        $pdf->SetXY($xPenulis + 1.5, $y0 + ($rowH - count($penulis) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['penulis'] - 3, $lineH, $line, 0, 0, 'L');
    }

    // Kolom Kategori
    $xKategori = $xPenulis + $w['penulis'];
    $pdf->SetXY($xKategori, $y0);
    $pdf->Cell($w['kategori'], $rowH, '', 1, 0, 'L', true);
    foreach ($kategori as $i => $line) {
        $pdf->SetXY($xKategori + 1.5, $y0 + ($rowH - count($kategori) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['kategori'] - 3, $lineH, $line, 0, 0, 'L');
    }

    // Kolom Tahun
    $xTahun = $xKategori + $w['kategori'];
    $pdf->SetXY($xTahun, $y0);
    $pdf->Cell($w['tahun'], $rowH, $row['tahun_terbit'], 1, 0, 'C', true);

    // Kolom Penerbit
    $xPenerbit = $xTahun + $w['tahun'];
    $pdf->SetXY($xPenerbit, $y0);
    $pdf->Cell($w['penerbit'], $rowH, '', 1, 0, 'L', true);
    foreach ($penerbit as $i => $line) {
        $pdf->SetXY($xPenerbit + 1.5, $y0 + ($rowH - count($penerbit) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['penerbit'] - 3, $lineH, $line, 0, 0, 'L');
    }

    // 6. Pindah ke baris berikutnya
    $pdf->SetXY($marginKiri, $y0 + $rowH);
}

// ================= EKSEKUSI =================
gambarHeader($pdf, $w, $lineH, $marginKiri);

$q = mysqli_query($conn, "SELECT * FROM books ORDER BY id ASC");
$no = 1;
while ($r = mysqli_fetch_assoc($q)) {
    cetakBaris($pdf, $w, $lineH, $marginKiri, $r, $no, $no % 2 == 0);
    $no++;
}

$pdf->Output('D', 'laporan_data_buku.pdf');
?>