# 📚 Aplikasi Data Buku Perpustakaan

Aplikasi web berbasis PHP + MySQL untuk mengelola data buku perpustakaan dengan fitur CRUD, Login Admin, Dashboard Statistik, Export Excel & PDF, dan JSON API.

## 🚀 Fitur

- 🔐 Login Admin (session-based)
- 📊 Dashboard statistik (total buku, kategori, penulis, penerbit)
- 📈 Grafik statistik (doughnut + bar chart)
- 📖 CRUD Data Buku (Tambah, Edit, Hapus, Lihat)
- 🔍 Pencarian buku realtime
- 📁 Filter berdasarkan kategori
- 🔢 Sorting tahun & judul
- 📄 Pagination (5/10/25/50 per halaman)
- 🖼️ Upload foto sampul buku
- 🌙 Dark mode toggle
- 📥 Export Excel (semua / hasil filter)
- 📄 Export PDF (rapi, auto-wrap)
- 🍬 SweetAlert untuk notifikasi
- 📱 Responsive (desktop, tablet, HP)

## 🛠️ Teknologi

- **Backend:** PHP 7+
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Library:** FPDF (export PDF), Chart.js (grafik), SweetAlert2 (notif)

## 📋 Cara Install

### 1. Clone / Download project

Taruh folder `perpus_api` di dalam `htdocs` XAMPP:
C:\xampp\htdocs\perpus_api\

### 2. Import database

- Buka **`http://localhost/phpmyadmin`**
- Klik tab **Import** → pilih file `perpus_api.sql`
- Klik **Go** → database `perpus_api` otomatis dibuat

Atau via CMD:

```bash
cd C:\xampp\mysql\bin
mysql -u root -p < C:\xampp\htdocs\perpus_api\perpus_api.sql