<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$qKat = mysqli_query($conn, "SELECT DISTINCT kategori FROM books ORDER BY kategori ASC");
$kategoriList = [];
while ($r = mysqli_fetch_assoc($qKat)) $kategoriList[] = $r['kategori'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Buku</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<nav>
  <span class="brand">📚 Perpustakaan</span>
  <a href="dashboard.php" class="nav-link">Dashboard</a>
  <a href="buku.php" class="nav-link">Data Buku</a>
  <a href="tambah.php" class="nav-link">Tambah Buku</a>
  <a href="export_excel.php" class="nav-link">Export Excel</a>
  <a href="export_pdf.php" class="nav-link">Export PDF</a>
  <a href="logout.php" class="nav-link" onclick="return konfirmasiLogout(event)">Logout</a>
  <button class="dark-toggle" onclick="toggleDark()" id="darkBtnDesktop">🌙</button>
  <button class="hamburger" onclick="toggleMenu(event)" id="hamburgerBtn">☰</button>
  <div class="nav-menu" id="navMenu">
    <a href="dashboard.php">Dashboard</a>
    <a href="buku.php">Data Buku</a>
    <a href="tambah.php">Tambah Buku</a>
    <a href="export_excel.php">Export Excel</a>
    <a href="export_pdf.php">Export PDF</a>
    <a href="logout.php" onclick="return konfirmasiLogout(event)">Logout</a>
    <button class="dark-toggle-menu" onclick="toggleDark()" id="darkBtnMenu">🌙 Dark Mode</button>
  </div>
</nav>

<div class="container">
  <h1>📖 Data Buku</h1>

  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari judul, penulis, kategori, atau penerbit...">
  </div>

  <div class="filter-bar">
    <select id="filterKategori">
      <option value="">📁 Semua Kategori</option>
      <?php foreach ($kategoriList as $k): ?>
        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
      <?php endforeach; ?>
    </select>

    <select id="sortTahun">
      <option value="">🔢 Urut Tahun</option>
      <option value="asc">Tahun Terlama → Terbaru</option>
      <option value="desc">Tahun Terbaru → Terlama</option>
    </select>

    <select id="sortJudul">
      <option value="">🔤 Urut Judul</option>
      <option value="asc">Judul A → Z</option>
      <option value="desc">Judul Z → A</option>
    </select>

    <select id="perPage">
      <option value="5">5 / halaman</option>
      <option value="10" selected>10 / halaman</option>
      <option value="25">25 / halaman</option>
      <option value="50">50 / halaman</option>
    </select>

    <button class="btn-export-filter" onclick="exportHasilFilter()">📥 Export Hasil Filter</button>
  </div>

  <p style="color:#7a8a80;font-size:13px;margin:10px 0;">
    Menampilkan <b id="jumlahTampil">0</b> dari <b id="jumlahTotal">0</b> buku
  </p>

  <div class="table-wrap">
    <table id="tabelBuku">
      <thead>
        <tr>
          <th>No</th><th>Cover</th><th>Kode</th><th>Judul</th><th>Penulis</th>
          <th>Kategori</th><th>Tahun</th><th>Penerbit</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="pagination" id="pagination"></div>
</div>

<script>
// ================== HAMBURGER MENU ==================
function toggleMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('navMenu');
  const btn = document.getElementById('hamburgerBtn');
  menu.classList.toggle('open');
  btn.textContent = menu.classList.contains('open') ? '✕' : '☰';
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('navMenu');
  const btn = document.getElementById('hamburgerBtn');
  if (menu && menu.classList.contains('open') && !menu.contains(e.target) && e.target !== btn) {
    menu.classList.remove('open');
    btn.textContent = '☰';
  }
});

// ================== DARK MODE ==================
function toggleDark() {
  document.body.classList.toggle('dark');
  const isDark = document.body.classList.contains('dark');
  localStorage.setItem('darkMode', isDark ? '1' : '0');
  updateDarkButtons(isDark);
}
function updateDarkButtons(isDark) {
  const btnD = document.getElementById('darkBtnDesktop');
  const btnM = document.getElementById('darkBtnMenu');
  if (btnD) btnD.textContent = isDark ? '☀️' : '🌙';
  if (btnM) btnM.textContent = isDark ? '☀️ Dark Mode' : '🌙 Dark Mode';
}
if (localStorage.getItem('darkMode') === '1') {
  document.body.classList.add('dark');
  updateDarkButtons(true);
} else {
  updateDarkButtons(false);
}

// ================== LOGOUT ==================
function konfirmasiLogout(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Logout?', text: 'Anda akan keluar dari dashboard admin.', icon: 'question',
    showCancelButton: true, confirmButtonText: 'Ya, Logout', cancelButtonText: 'Batal',
    confirmButtonColor: '#8fa998', cancelButtonColor: '#a8c0b0'
  }).then((r) => { if (r.isConfirmed) window.location.href = 'logout.php'; });
  return false;
}

// ================== DATA ==================
let allBooks = [];
let filteredBooks = [];
let currentPage = 1;

fetch("data_json.php")
  .then(r => r.json())
  .then(data => {
    allBooks = data;
    filteredBooks = data;
    document.getElementById('jumlahTotal').textContent = data.length;
    applyFilter(true);
  })
  .catch(err => Swal.fire({
    icon: 'error', title: 'Gagal ambil data', text: err.message,
    confirmButtonColor: '#8fa998'
  }));

// ================== RENDER ==================
function renderTable(data) {
  const tbody = document.querySelector("#tabelBuku tbody");
  tbody.innerHTML = "";
  document.getElementById('jumlahTampil').textContent = data.length;

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;color:#8a9a90;padding:30px;">Tidak ada data ditemukan</td></tr>`;
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  const perPage = parseInt(document.getElementById('perPage').value);
  const totalPages = Math.ceil(data.length / perPage);
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const pageData = data.slice(start, end);

  pageData.forEach((b, i) => {
    const nomor = start + i + 1;
    let coverHTML;
    if (b.cover && b.cover !== '') {
      coverHTML = `<img src="uploads/${b.cover}" alt="cover" class="cover-img">`;
    } else {
      const warna = pilihWarna(b.id);
      coverHTML = `<div class="recent-cover" style="background: linear-gradient(135deg, ${warna[0]}, ${warna[1]});">${b.judul.charAt(0).toUpperCase()}</div>`;
    }

    tbody.innerHTML += `
      <tr>
        <td>${nomor}</td>
        <td>${coverHTML}</td>
        <td>${b.kode_buku}</td>
        <td>${b.judul}</td>
        <td>${b.penulis}</td>
        <td>${b.kategori}</td>
        <td>${b.tahun_terbit}</td>
        <td>${b.penerbit}</td>
        <td>
          <a href="edit.php?id=${b.id}" class="btn btn-edit">Edit</a>
          <button class="btn btn-hapus" onclick="hapusBuku(${b.id}, '${b.judul.replace(/'/g, "\\'")}')">Hapus</button>
        </td>
      </tr>`;
  });

  renderPagination(totalPages);
}

function pilihWarna(id) {
  const colors = [
    ['#8fa998','#6f8a7a'], ['#a8c0b0','#8fa998'], ['#d4b483','#c9a570'],
    ['#c98a8a','#b06f6f'], ['#9eb5c2','#7a9aab'], ['#b8a8c9','#9a8aab'],
    ['#a8c9b5','#8aab97']
  ];
  return colors[id % colors.length];
}

// ================== PAGINATION ==================
function renderPagination(totalPages) {
  const pag = document.getElementById('pagination');
  pag.innerHTML = '';
  if (totalPages <= 1) return;

  const prev = document.createElement('button');
  prev.textContent = '« Prev';
  prev.className = 'page-btn' + (currentPage === 1 ? ' disabled' : '');
  prev.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(filteredBooks); } };
  pag.appendChild(prev);

  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);
  if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

  if (startPage > 1) {
    pag.appendChild(buatPageBtn(1));
    if (startPage > 2) pag.appendChild(buatEllipsis());
  }
  for (let i = startPage; i <= endPage; i++) pag.appendChild(buatPageBtn(i));
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) pag.appendChild(buatEllipsis());
    pag.appendChild(buatPageBtn(totalPages));
  }

  const next = document.createElement('button');
  next.textContent = 'Next »';
  next.className = 'page-btn' + (currentPage === totalPages ? ' disabled' : '');
  next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(filteredBooks); } };
  pag.appendChild(next);
}

function buatPageBtn(num) {
  const btn = document.createElement('button');
  btn.textContent = num;
  btn.className = 'page-btn' + (num === currentPage ? ' active' : '');
  btn.onclick = () => { currentPage = num; renderTable(filteredBooks); };
  return btn;
}
function buatEllipsis() {
  const span = document.createElement('span');
  span.textContent = '...';
  span.style.padding = '0 6px';
  span.style.color = '#7a8a80';
  return span;
}

// ================== FILTER ==================
function applyFilter(resetPage = true) {
  const keyword  = document.getElementById('searchInput').value.toLowerCase();
  const kategori = document.getElementById('filterKategori').value;
  const sortThn  = document.getElementById('sortTahun').value;
  const sortJdl  = document.getElementById('sortJudul').value;

  let result = allBooks.filter(b => {
    const cocokKeyword =
      b.judul.toLowerCase().includes(keyword) ||
      b.penulis.toLowerCase().includes(keyword) ||
      b.kategori.toLowerCase().includes(keyword) ||
      b.penerbit.toLowerCase().includes(keyword) ||
      b.kode_buku.toLowerCase().includes(keyword);
    const cocokKategori = !kategori || b.kategori === kategori;
    return cocokKeyword && cocokKategori;
  });

  if (sortThn === 'asc')  result.sort((a,b) => a.tahun_terbit - b.tahun_terbit);
  if (sortThn === 'desc') result.sort((a,b) => b.tahun_terbit - a.tahun_terbit);
  if (sortJdl === 'asc')  result.sort((a,b) => a.judul.localeCompare(b.judul));
  if (sortJdl === 'desc') result.sort((a,b) => b.judul.localeCompare(a.judul));

  filteredBooks = result;
  if (resetPage) currentPage = 1;
  renderTable(result);
}

document.getElementById('searchInput').addEventListener('input', () => applyFilter(true));
document.getElementById('filterKategori').addEventListener('change', () => applyFilter(true));
document.getElementById('sortTahun').addEventListener('change', () => applyFilter(true));
document.getElementById('sortJudul').addEventListener('change', () => applyFilter(true));
document.getElementById('perPage').addEventListener('change', () => applyFilter(true));

// ================== EXPORT HASIL FILTER (Excel / PDF) ==================
function exportHasilFilter() {
  if (filteredBooks.length === 0) {
    Swal.fire({
      icon: 'info',
      title: 'Tidak ada data',
      text: 'Filter tidak menghasilkan data apapun.',
      confirmButtonColor: '#8fa998'
    });
    return;
  }

  // Ambil info filter aktif
  const keyword  = document.getElementById('searchInput').value.trim();
  const kategori = document.getElementById('filterKategori').value;
  const sortThn  = document.getElementById('sortTahun').value;
  const sortJdl  = document.getElementById('sortJudul').value;

  // Bikin keterangan filter
  let ketFilter = [];
  if (kategori) ketFilter.push('📁 Kategori: <b>' + kategori + '</b>');
  if (keyword)  ketFilter.push('🔍 Pencarian: <b>"' + keyword + '"</b>');
  if (sortThn)  ketFilter.push('🔢 Urut tahun: <b>' + (sortThn === 'asc' ? 'Terlama→Terbaru' : 'Terbaru→Terlama') + '</b>');
  if (sortJdl)  ketFilter.push('🔤 Urut judul: <b>' + (sortJdl === 'asc' ? 'A→Z' : 'Z→A') + '</b>');

  const htmlFilter = ketFilter.length > 0
    ? '<div style="background:#f5f7f4;padding:10px 12px;border-radius:8px;font-size:12px;text-align:left;margin:10px 0;line-height:1.7;color:#3d4a42;">' + ketFilter.join('<br>') + '</div>'
    : '<p style="font-size:13px;color:#7a8a80;margin:10px 0;">Tanpa filter — semua data akan di-export.</p>';

  Swal.fire({
    title: '📥 Export Hasil Filter',
    html: `
      <p style="font-size:14px;color:#3d4a42;margin-bottom:6px;">
        Akan export <b style="color:#6f8a7a;">${filteredBooks.length}</b> data.
      </p>
      ${htmlFilter}
      <p style="font-size:13px;color:#7a8a80;margin-top:14px;margin-bottom:6px;">
        Pilih format export:
      </p>
    `,
    icon: 'question',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '📊 Excel',
    denyButtonText: '📄 PDF',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#8fa998',
    denyButtonColor: '#d4b483',
    cancelButtonColor: '#a8c0b0',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      // ==== EXPORT EXCEL ====
      submitExport('export_filter.php', 'Excel');
    } else if (result.isDenied) {
      // ==== EXPORT PDF ====
      submitExport('export_filter_pdf.php', 'PDF');
    }
  });
}

// Helper: submit form ke server
function submitExport(action, label) {
  const keyword  = document.getElementById('searchInput').value.trim();
  const kategori = document.getElementById('filterKategori').value;
  const sortThn  = document.getElementById('sortTahun').value;
  const sortJdl  = document.getElementById('sortJudul').value;

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = action;
  form.target = '_blank';  // buka di tab baru

  const fields = {
    data: JSON.stringify(filteredBooks),
    kategori: kategori,
    keyword: keyword,
    sortTahun: sortThn,
    sortJudul: sortJdl
  };

  for (const key in fields) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = fields[key];
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);

  // Notif sukses
  Swal.fire({
    icon: 'success',
    title: 'Download Dimulai',
    text: `File ${label} lagi diproses. Cek folder Download.`,
    confirmButtonColor: '#8fa998',
    timer: 2500,
    showConfirmButton: false
  });
}

// ================== HAPUS ==================
function hapusBuku(id, judul) {
  Swal.fire({
    title: 'Yakin hapus?',
    html: `Data buku <b>"${judul}"</b> akan dihapus permanen.`,
    icon: 'warning', showCancelButton: true,
    confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
    confirmButtonColor: '#c98a8a', cancelButtonColor: '#a8c0b0',
    reverseButtons: true
  }).then((r) => { if (r.isConfirmed) window.location.href = `hapus.php?id=${id}`; });
}

// ================== NOTIF STATUS ==================
const urlParams = new URLSearchParams(window.location.search);
const status = urlParams.get('status');
if (status) {
  const pesan = {
    tambah:   { icon:'success', title:'Berhasil!', text:'Data buku berhasil ditambahkan.' },
    edit:     { icon:'success', title:'Berhasil!', text:'Data buku berhasil diperbarui.' },
    hapus:    { icon:'success', title:'Terhapus!', text:'Data buku berhasil dihapus.' },
    notfound: { icon:'error',   title:'Tidak Ditemukan', text:'Data buku tidak ada di database.' },
    gagal:    { icon:'error',   title:'Gagal', text:'Terjadi kesalahan pada server.' }
  };
  if (pesan[status]) {
    Swal.fire({
      icon: pesan[status].icon,
      title: pesan[status].title,
      text: pesan[status].text,
      confirmButtonColor: '#8fa998',
      timer: 2000, showConfirmButton: false
    });
    window.history.replaceState({}, document.title, 'buku.php');
  }
}
</script>
</body>
</html>