<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
require('fpdf/fpdf.php');

$data = json_decode($_POST['data'] ?? '[]', true);
$kategori = $_POST['kategori'] ?? '';
$keyword  = $_POST['keyword'] ?? '';
$sortTahun = $_POST['sortTahun'] ?? '';
$sortJudul = $_POST['sortJudul'] ?? '';

// Bikin judul filter
$judulFilter = 'SEMUA DATA';
if ($kategori && $keyword) {
    $judulFilter = 'Kategori "' . strtoupper($kategori) . '" + Pencarian "' . $keyword . '"';
} elseif ($kategori) {
    $judulFilter = 'Kategori: ' . strtoupper($kategori);
} elseif ($keyword) {
    $judulFilter = 'Pencarian: "' . $keyword . '"';
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// ===== HEADER =====
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN DATA BUKU PERPUSTAKAAN', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(111, 138, 122);
$pdf->Cell(0, 8, $judulFilter, 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, 'Dicetak: ' . date('d F Y, H:i') . ' WIB | Total: ' . count($data) . ' buku', 0, 1, 'C');
$pdf->Ln(3);

// ===== KONFIGURASI KOLOM =====
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

// ===== FUNGSI BANTU =====
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

function cetakBaris($pdf, $w, $lineH, $marginKiri, $row, $no, $zebra) {
    $judul    = pecahTeks($pdf, $row['judul'],    $w['judul']);
    $penulis  = pecahTeks($pdf, $row['penulis'],  $w['penulis']);
    $kategori = pecahTeks($pdf, $row['kategori'], $w['kategori']);
    $penerbit = pecahTeks($pdf, $row['penerbit'], $w['penerbit']);

    $maxBaris = max(count($judul), count($penulis), count($kategori), count($penerbit), 1);
    $rowH = $maxBaris * $lineH;

    if ($pdf->GetY() + $rowH > 285) {
        $pdf->AddPage();
        gambarHeader($pdf, $w, $lineH, $marginKiri);
    }

    $y0 = $pdf->GetY();

    if ($zebra) $pdf->SetFillColor(240, 245, 240);
    else        $pdf->SetFillColor(255, 255, 255);

    $pdf->SetXY($marginKiri, $y0);
    $pdf->Cell($w['no'], $rowH, $no, 1, 0, 'C', true);

    $pdf->SetXY($marginKiri + $w['no'], $y0);
    $pdf->Cell($w['kode'], $rowH, $row['kode_buku'], 1, 0, 'C', true);

    $pdf->SetXY($marginKiri + $w['no'] + $w['kode'], $y0);
    $pdf->Cell($w['judul'], $rowH, '', 1, 0, 'L', true);
    foreach ($judul as $i => $line) {
        $pdf->SetXY($marginKiri + $w['no'] + $w['kode'] + 1.5, $y0 + ($rowH - count($judul) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['judul'] - 3, $lineH, $line, 0, 0, 'L');
    }

    $xPenulis = $marginKiri + $w['no'] + $w['kode'] + $w['judul'];
    $pdf->SetXY($xPenulis, $y0);
    $pdf->Cell($w['penulis'], $rowH, '', 1, 0, 'L', true);
    foreach ($penulis as $i => $line) {
        $pdf->SetXY($xPenulis + 1.5, $y0 + ($rowH - count($penulis) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['penulis'] - 3, $lineH, $line, 0, 0, 'L');
    }

    $xKategori = $xPenulis + $w['penulis'];
    $pdf->SetXY($xKategori, $y0);
    $pdf->Cell($w['kategori'], $rowH, '', 1, 0, 'L', true);
    foreach ($kategori as $i => $line) {
        $pdf->SetXY($xKategori + 1.5, $y0 + ($rowH - count($kategori) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['kategori'] - 3, $lineH, $line, 0, 0, 'L');
    }

    $xTahun = $xKategori + $w['kategori'];
    $pdf->SetXY($xTahun, $y0);
    $pdf->Cell($w['tahun'], $rowH, $row['tahun_terbit'], 1, 0, 'C', true);

    $xPenerbit = $xTahun + $w['tahun'];
    $pdf->SetXY($xPenerbit, $y0);
    $pdf->Cell($w['penerbit'], $rowH, '', 1, 0, 'L', true);
    foreach ($penerbit as $i => $line) {
        $pdf->SetXY($xPenerbit + 1.5, $y0 + ($rowH - count($penerbit) * $lineH) / 2 + $i * $lineH);
        $pdf->Cell($w['penerbit'] - 3, $lineH, $line, 0, 0, 'L');
    }

    $pdf->SetXY($marginKiri, $y0 + $rowH);
}

// ===== EKSEKUSI =====
gambarHeader($pdf, $w, $lineH, $marginKiri);

$no = 1;
foreach ($data as $b) {
    cetakBaris($pdf, $w, $lineH, $marginKiri, $b, $no, $no % 2 == 0);
    $no++;
}

// ===== FOOTER =====
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 8, 'Total Data: ' . count($data) . ' buku', 0, 1, 'L');

$pdf->Output('D', 'hasil_filter_buku.pdf');
?>