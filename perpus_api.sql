-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Sep 2026 pada 07.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpus_api`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(20) DEFAULT NULL,
  `judul` varchar(150) DEFAULT NULL,
  `penulis` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `kode_buku`, `judul`, `penulis`, `kategori`, `tahun_terbit`, `penerbit`, `cover`) VALUES
(1, 'BK001', 'Laskar Pelangi', 'Andrea Hirata', 'Novel', '2005', 'Bentang Pustak', 'BK001_1790224328.jpg'),
(2, 'BK002', 'Bumi', 'Tere Liye', 'Novel', '2014', 'Gramedia', NULL),
(4, 'BK004', 'Belajar HTML dan CSS', 'Jubilee Enterprise', 'Teknologi', '2021', 'Elex Media', NULL),
(5, 'BK005', 'Dasar-Dasar JavaScript', 'Wahana Komputer', 'Teknologi', '2022', 'Andi', NULL),
(6, 'BK006', 'Negeri 5 Menara', 'Ahmad Fuadi', 'Novel', '2009', 'Gramedia', NULL),
(7, 'BK007', 'Filosofi Teras', 'Henry Manampiring', 'Filsafat', '2018', 'Kompas', 'BK007_1790224411.jpg'),
(8, 'BK008', 'Clean Code', 'Robert C. Martin', 'Teknologi', '2008', 'Prentice Hall', NULL),
(9, 'BK009', 'Atomic Habits', 'James Clear', 'Self Improvement', '2018', 'Gramedia', NULL),
(10, 'BK010', 'Sapiens', 'Yuval Noah Harari', 'Sejarah', '2011', 'Kepustakaan Populer', 'BK010_1790224439.jpg'),
(11, 'BK003', 'Cara menjadi seorang yang tidak berguna', 'Adrey', 'Self Improvement', '2026', 'Terbentang', NULL),
(12, 'BK011', 'Cara Nyawit', 'Adrey', 'Self Improvement', '2026', 'Terbentang', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
