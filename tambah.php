<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$error = '';
$old = ['kode_buku'=>'','judul'=>'','penulis'=>'','kategori'=>'','tahun_terbit'=>'','penerbit'=>''];

if (isset($_POST['simpan'])) {
    $old = [
        'kode_buku' => trim($_POST['kode_buku']),
        'judul' => trim($_POST['judul']),
        'penulis' => trim($_POST['penulis']),
        'kategori' => trim($_POST['kategori']),
        'tahun_terbit' => trim($_POST['tahun_terbit']),
        'penerbit' => trim($_POST['penerbit'])
    ];

    if (!preg_match('/^BK\d{3}$/', $old['kode_buku'])) $error = 'Format kode buku harus BK diikuti 3 angka.';
    elseif (strlen($old['judul']) < 3) $error = 'Judul minimal 3 karakter.';
    elseif (strlen($old['penulis']) < 3) $error = 'Nama penulis minimal 3 karakter.';
    elseif (empty($old['kategori'])) $error = 'Kategori wajib diisi.';
    elseif ((int)$old['tahun_terbit'] < 1900 || (int)$old['tahun_terbit'] > (date('Y') + 1)) $error = 'Tahun terbit tidak valid.';
    elseif (strlen($old['penerbit']) < 3) $error = 'Nama penerbit minimal 3 karakter.';
    else {
        $kodeEsc = mysqli_real_escape_string($conn, $old['kode_buku']);
        $cek = mysqli_query($conn, "SELECT id FROM books WHERE kode_buku = '$kodeEsc'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Kode buku ' . htmlspecialchars($old['kode_buku']) . ' sudah dipakai!';
        } else {
            $namaFileCover = null;
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['cover'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (!in_array($ext, $allowed)) $error = 'Format gambar harus JPG, PNG, atau WEBP.';
                elseif ($file['size'] > 2 * 1024 * 1024) $error = 'Ukuran gambar maksimal 2MB.';
                else {
                    $namaFileCover = $old['kode_buku'] . '_' . time() . '.' . $ext;
                    $tujuan = __DIR__ . '/uploads/' . $namaFileCover;
                    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
                        $error = 'Gagal mengupload gambar.';
                        $namaFileCover = null;
                    }
                }
            }
            if (!$error) {
                $stmt = mysqli_prepare($conn, "INSERT INTO books (kode_buku, judul, penulis, kategori, tahun_terbit, penerbit, cover) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'ssssiss', $old['kode_buku'], $old['judul'], $old['penulis'], $old['kategori'], $old['tahun_terbit'], $old['penerbit'], $namaFileCover);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: buku.php?status=tambah"); exit;
                } else $error = 'Gagal menyimpan: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Buku</title>
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
  <h1>Tambah Buku</h1>
  <p style="color:#7a8a80;font-size:13px;margin-bottom:15px;">Isi kolom bertanda <span style="color:#c98a8a;">*</span>. Upload foto sampul opsional.</p>

  <div class="form-box">
    <form method="post" enctype="multipart/form-data" onsubmit="return validasiForm(event)">
      <label>Kode Buku <span style="color:#c98a8a;">*</span></label>
      <input type="text" name="kode_buku" id="kode_buku" value="<?= htmlspecialchars($old['kode_buku']) ?>" placeholder="Contoh: BK011" required autofocus>

      <label>Judul <span style="color:#c98a8a;">*</span></label>
      <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($old['judul']) ?>" placeholder="Judul buku" required>

      <label>Penulis <span style="color:#c98a8a;">*</span></label>
      <input type="text" name="penulis" id="penulis" value="<?= htmlspecialchars($old['penulis']) ?>" placeholder="Nama penulis" required>

      <label>Kategori <span style="color:#c98a8a;">*</span></label>
      <input type="text" name="kategori" id="kategori" value="<?= htmlspecialchars($old['kategori']) ?>" placeholder="Contoh: Novel, Teknologi" required>

      <label>Tahun Terbit <span style="color:#c98a8a;">*</span></label>
      <input type="number" name="tahun_terbit" id="tahun_terbit" value="<?= htmlspecialchars($old['tahun_terbit']) ?>" min="1900" max="<?= date('Y') + 1 ?>" required>

      <label>Penerbit <span style="color:#c98a8a;">*</span></label>
      <input type="text" name="penerbit" id="penerbit" value="<?= htmlspecialchars($old['penerbit']) ?>" placeholder="Nama penerbit" required>

      <label>📸 Foto Sampul (opsional)</label>
      <input type="file" name="cover" id="cover" accept="image/*" onchange="previewCover(event)">
      <small style="color:#8a9a90;font-size:12px;">Format: JPG/PNG/WEBP. Maks 2MB.</small>

      <div id="coverPreview" style="margin-top:12px; display:none;">
        <img id="previewImg" style="max-width:120px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
      </div>

      <button type="submit" name="simpan" class="btn btn-primary">SIMPAN</button>
      <a href="buku.php" class="btn" style="background:#e0e6e0;color:#4a5a50;margin-left:8px;">← Batal</a>
    </form>
  </div>
</div>

<script>
function previewCover(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(ev) {
    document.getElementById('previewImg').src = ev.target.result;
    document.getElementById('coverPreview').style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function validasiForm(e) {
  const kode = document.getElementById('kode_buku').value.trim();
  const judul = document.getElementById('judul').value.trim();
  const penulis = document.getElementById('penulis').value.trim();
  const kat = document.getElementById('kategori').value.trim();
  const tahun = parseInt(document.getElementById('tahun_terbit').value);
  const penerbit = document.getElementById('penerbit').value.trim();
  const maxTahun = <?= date('Y') + 1 ?>;

  if (!/^BK\d{3}$/.test(kode)) { e.preventDefault(); Swal.fire({icon:'warning',title:'Kode Buku Salah',text:'Format: BK + 3 angka. Contoh: BK011',confirmButtonColor:'#8fa998'}); return false; }
  if (judul.length < 3) { e.preventDefault(); Swal.fire({icon:'warning',title:'Judul Pendek',text:'Min 3 karakter.',confirmButtonColor:'#8fa998'}); return false; }
  if (penulis.length < 3) { e.preventDefault(); Swal.fire({icon:'warning',title:'Penulis Pendek',text:'Min 3 karakter.',confirmButtonColor:'#8fa998'}); return false; }
  if (kat === '') { e.preventDefault(); Swal.fire({icon:'warning',title:'Kategori Kosong',text:'Wajib diisi.',confirmButtonColor:'#8fa998'}); return false; }
  if (isNaN(tahun) || tahun < 1900 || tahun > maxTahun) { e.preventDefault(); Swal.fire({icon:'warning',title:'Tahun Salah',text:`Antara 1900 - ${maxTahun}.`,confirmButtonColor:'#8fa998'}); return false; }
  if (penerbit.length < 3) { e.preventDefault(); Swal.fire({icon:'warning',title:'Penerbit Pendek',text:'Min 3 karakter.',confirmButtonColor:'#8fa998'}); return false; }

  const fileInput = document.getElementById('cover');
  if (fileInput.files.length > 0) {
    const file = fileInput.files[0];
    const allowed = ['image/jpeg','image/png','image/webp'];
    if (!allowed.includes(file.type)) { e.preventDefault(); Swal.fire({icon:'warning',title:'Format Salah',text:'JPG/PNG/WEBP aja.',confirmButtonColor:'#8fa998'}); return false; }
    if (file.size > 2 * 1024 * 1024) { e.preventDefault(); Swal.fire({icon:'warning',title:'Kegedean',text:'Maks 2MB.',confirmButtonColor:'#8fa998'}); return false; }
  }
  return true;
}

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
</script>

<?php if ($error): ?>
<script>
Swal.fire({ icon:'error', title:'Gagal Menyimpan', text:'<?= htmlspecialchars($error, ENT_QUOTES) ?>', confirmButtonColor:'#8fa998' });
</script>
<?php endif; ?>
</body>
</html>